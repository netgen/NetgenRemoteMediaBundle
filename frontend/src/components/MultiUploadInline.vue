<template>
  <div class="multi-upload-inline">
    <!-- Dropzone for drag & drop -->
    <div
      class="dropzone"
      :class="{ 'dragover': isDragOver, 'has-files': files.length > 0, 'disabled': isAtLimit }"
      @drop.prevent="handleDrop"
      @dragover.prevent="!isAtLimit && (isDragOver = true)"
      @dragleave.prevent="isDragOver = false"
      @click="!isAtLimit && triggerFileInput()"
    >
      <div class="dropzone-content">
        <i class="ng-icon ng-upload-cloud"></i>
        <p>{{ dropZoneLabel }}</p>
        <button type="button" 
                class="btn btn-blue"
                :disabled="isAtLimit"
                @click.stop="triggerFileInput">
          {{ browseLabel }}
        </button>
      </div>
      <input
        ref="fileInput"
        type="file"
        :multiple="uploadLimit !== 1"
        :accept="acceptedFileTypes"
        hidden
        :disabled="isAtLimit"
        @change="handleFileSelect"
      />
    </div>
    
    <!-- Limit indicator (only show when there are pending files or we're over limit) -->
    <div v-if="hasLimit && (pendingFilesCount > 0 || isOverLimit)"
         class="limit-indicator"
         :class="limitClass">
      <i v-if="isOverLimit" class="fa fa-exclamation-triangle"></i>
      <i v-else-if="isAtLimit" class="fa fa-info-circle"></i>
      <i v-else class="fa fa-check-circle"></i>
      <span>{{ limitMessage }}</span>
    </div>

    <!-- Transient banner for blocked/clamped selection attempts -->
    <div v-if="notice"
         class="limit-indicator"
         :class="notice.severity === 'error' ? 'limit-exceeded' : 'limit-reached'"
         role="alert">
      <i :class="notice.severity === 'error' ? 'fa fa-exclamation-triangle' : 'fa fa-info-circle'"></i>
      <span>{{ notice.message }}</span>
    </div>

    <!-- Global settings (shown when files are added) -->
    <div v-if="files.length > 0" class="global-settings">
      <!-- Global folder selector (shown when no folder is pre-configured) -->
      <div v-if="!config.folder" class="global-setting-item global-folder">
        <label>{{ config.translations.multi_upload_global_folder }}</label>
        <folder-tree-select
          :config="config"
          :selected-folder="globalFolder"
          @change="handleGlobalFolderChange"
        ></folder-tree-select>
      </div>

      <!-- Global visibility selector -->
      <div v-if="visibilities.length > 0" class="global-setting-item global-visibility">
        <label>{{ config.translations.multi_upload_global_visibility }}</label>
        <v-select
          :options="visibilities"
          label="name"
          v-model="globalVisibility"
          :reduce="option => option.id"
          :clearable="false"
          @input="handleGlobalVisibilityChange"
        />
      </div>

      <!-- Global overwrite checkbox -->
      <div class="global-setting-item global-overwrite">
        <label>
          <input type="checkbox" v-model="globalOverwrite" @change="handleGlobalOverwriteChange" />
          {{ uploadOverwriteLabel }} ({{ config.translations.multi_upload_global_apply_to_all }})
        </label>
      </div>
    </div>

    <!-- File list table -->
    <multi-upload-table
      v-if="files.length > 0"
      :files="files"
      :config="config"
      :visibilities="visibilities"
      :is-over-limit="isOverLimit"
      :upload-limit="uploadLimit"
      :batch-uploading="uploading"
      @update-file="handleFileUpdate"
      @remove-file="handleFileRemove"
      @upload-single="uploadSingleFile"
      @use-existing="useExistingResource"
    />

    <!-- Overall Progress Bar (shown when uploading) -->
    <div v-if="uploading && totalFiles > 0" class="overall-progress">
      <div class="progress-info">
        <span>{{ uploadingLabel }} {{ currentFileIndex }}/{{ totalFiles }}</span>
        <span>{{ overallProgress }}%</span>
      </div>
      <div class="progress-bar-wrapper">
        <div class="progress-bar-fill" :style="{ width: overallProgress + '%' }"></div>
      </div>
    </div>

    <!-- Actions -->
    <div class="multi-upload-actions" v-if="files.length > 0">
      <div class="upload-summary">
        <span class="pending-count">{{ pendingCount }} {{ filesPendingLabel }}</span>
        <span class="success-count" v-if="successCount > 0">{{ successCount }} {{ filesSuccessLabel }}</span>
        <span class="error-count" v-if="errorCount > 0">{{ errorCount }} {{ filesErrorLabel }}</span>
      </div>
      <button
        type="button"
        class="btn btn-blue"
        :disabled="uploading || pendingCount === 0 || isOverLimit || !canUpload()"
        @click="uploadAll"
      >
        {{ uploading ? uploadingLabel : uploadAllLabel }}
      </button>
      <button
        type="button"
        class="btn btn-default"
        :disabled="uploading"
        @click="clearCompleted"
      >
        {{ clearCompletedLabel }}
      </button>
    </div>
  </div>
</template>

<script>
import MultiUploadTable from "./MultiUploadTable";
import FolderTreeSelect from "./FolderTreeSelect";
import vSelect from "vue-select";
import axios from "axios";

export default {
  name: "MultiUploadInline",
  props: {
    config: Object,
    visibilities: Array,
    uploadLimit: {
      type: Number,
      default: 0  // 0 = unlimited
    },
    currentCount: {
      type: Number,
      default: 0
    }
  },
  components: {
    'multi-upload-table': MultiUploadTable,
    'folder-tree-select': FolderTreeSelect,
    'v-select': vSelect,
  },
  data() {
    return {
      uploading: false,
      isDragOver: false,
      files: [], // Array of {id, file, filename, folder, visibility, overwrite, status, progress, error, resource}
      nextId: 1,
      globalFolder: '',
      globalVisibility: '',
      globalOverwrite: false,
      currentFileIndex: 0,
      totalFiles: 0,
      notice: null, // {message, severity: 'info'|'error'} — transient banner replacing alert()
      noticeTimer: null,
    };
  },
  computed: {
    isSingleFileMode() {
      return this.uploadLimit === 1;
    },
    dropZoneLabel() {
      if (this.isSingleFileMode) {
        return this.config.translations.multi_upload_drop_zone_single || 'Drop file here or click to browse';
      }
      return this.config.translations.multi_upload_drop_zone || 'Drop files here or click to browse';
    },
    browseLabel() {
      if (this.isSingleFileMode) {
        return this.config.translations.multi_upload_browse_single || 'Browse File';
      }
      return this.config.translations.multi_upload_browse || 'Browse Files';
    },
    uploadOverwriteLabel() {
      return this.config.translations.upload_checkbox_overwrite || 'Overwrite';
    },
    filesPendingLabel() {
      return this.config.translations.multi_upload_files_pending || 'pending';
    },
    filesSuccessLabel() {
      return this.config.translations.multi_upload_files_success || 'uploaded';
    },
    filesErrorLabel() {
      return this.config.translations.multi_upload_files_error || 'failed';
    },
    uploadingLabel() {
      return this.config.translations.multi_upload_uploading || 'Uploading...';
    },
    uploadAllLabel() {
      return this.config.translations.multi_upload_button_upload_all || 'Upload All';
    },
    clearCompletedLabel() {
      return this.config.translations.multi_upload_clear_completed || 'Clear Completed';
    },
    hasLimit() {
      return this.uploadLimit > 0;
    },
    pendingFilesCount() {
      // Only count files that haven't been successfully uploaded yet
      return this.files.filter(f => f.status === 'pending' || f.status === 'uploading' || f.status === 'error').length;
    },
    remainingSlots() {
      if (!this.hasLimit) return Infinity;
      return Math.max(0, this.uploadLimit - this.currentCount - this.pendingFilesCount);
    },
    totalCount() {
      // Only count current uploaded files + pending/uploading files (not already uploaded files in queue)
      return this.currentCount + this.pendingFilesCount;
    },
    isAtLimit() {
      return this.hasLimit && this.totalCount >= this.uploadLimit;
    },
    isOverLimit() {
      return this.hasLimit && this.totalCount > this.uploadLimit;
    },
    limitMessage() {
      if (!this.hasLimit) return '';
      const t = this.config.translations || {};
      if (this.isOverLimit) {
        return (t.limit_exceeded || 'Too many files! Maximum %limit% allowed')
          .replace('%limit%', this.uploadLimit);
      }
      if (this.isAtLimit) {
        return (t.limit_reached || 'File limit reached (%limit% maximum)')
          .replace('%limit%', this.uploadLimit);
      }
      return (t.limit_remaining || '%remaining% slot(s) remaining')
        .replace('%remaining%', this.remainingSlots)
        .replace('%limit%', this.uploadLimit);
    },
    limitClass() {
      if (this.isOverLimit) return 'limit-exceeded';
      if (this.isAtLimit) return 'limit-reached';
      return '';
    },
    pendingCount() {
      return this.files.filter(f => f.status === 'pending').length;
    },
    successCount() {
      return this.files.filter(f => f.status === 'success').length;
    },
    errorCount() {
      return this.files.filter(f => f.status === 'error').length;
    },
    defaultVisibility() {
      if (this.globalVisibility) {
        return this.globalVisibility;
      }

      if (this.visibilities.length > 0) {
        return this.visibilities[0].id;
      }

      if ((this.config.allowedVisibilities || []).length > 0) {
        return this.config.allowedVisibilities[0];
      }

      return '';
    },
    overallProgress() {
      if (this.totalFiles === 0) return 0;
      const completedFiles = this.successCount + this.errorCount;
      const currentProgress = completedFiles / this.totalFiles * 100;
      return Math.round(currentProgress);
    },
    acceptedFileTypes() {
      const allowed = this.config.allowedTypes || [];
      if (allowed.length === 0) return null;

      const mimeMap = { image: 'image/*', video: 'video/*', audio: 'audio/*' };
      const accepts = allowed.map(type => mimeMap[type]).filter(Boolean);

      // Types without a MIME family (document, other) cannot be expressed in
      // accept=; don't restrict in that case. Server-side checks stay authoritative.
      if (accepts.length !== allowed.length) return null;

      return accepts.join(',');
    },
  },
  methods: {
    showNotice(message, severity = 'info') {
      this.notice = { message, severity };
      if (this.noticeTimer) clearTimeout(this.noticeTimer);
      this.noticeTimer = setTimeout(() => { this.notice = null; }, 5000);
    },
    triggerFileInput() {
      this.$refs.fileInput.click();
    },
    handleFileSelect(event) {
      const files = Array.from(event.target.files);
      // In single file mode, only take the first file
      const filesToAdd = this.isSingleFileMode && files.length > 0 ? [files[0]] : files;
      this.addFiles(filesToAdd);
      // Reset input so same file can be selected again
      event.target.value = '';
    },
    handleDrop(event) {
      this.isDragOver = false;
      const files = Array.from(event.dataTransfer.files);
      // In single file mode, only take the first file
      const filesToAdd = this.isSingleFileMode && files.length > 0 ? [files[0]] : files;
      this.addFiles(filesToAdd);
    },
    addFiles(files) {
      const filesArray = Array.from(files);
      const t = this.config.translations || {};

      if (this.hasLimit) {
        const availableSlots = this.uploadLimit - this.currentCount - this.pendingFilesCount;
        if (availableSlots <= 0) {
          this.showNotice(
            (t.limit_reached || 'File limit reached (%limit% maximum)').replace('%limit%', this.uploadLimit),
            'error'
          );
          return;
        }
        if (filesArray.length > availableSlots) {
          this.showNotice(
            (t.limit_remaining || '%remaining% slot(s) remaining').replace('%remaining%', availableSlots).replace('%limit%', this.uploadLimit),
            'info'
          );
          filesArray.splice(availableSlots);
        }
      }

      filesArray.forEach(file => {
        this.files.push({
          id: this.nextId++,
          file: file,
          filename: file.name,
          folder: this.config.folder ? this.config.folder.id : this.normalizeFolderValue(this.globalFolder),
          visibility: this.defaultVisibility,
          overwrite: this.globalOverwrite,
          status: 'pending', // pending, uploading, success, error
          progress: 0,
          error: null,
          resource: null,
        });
      });
    },
    canUpload() {
      return !this.isOverLimit && this.files.some(f => f.status === 'pending');
    },
    normalizeFolderValue(folder) {
      if (folder === null || typeof folder === 'undefined' || folder === '(root)' || folder === 'null') {
        return '';
      }

      return folder;
    },
    handleGlobalFolderChange(folder) {
      this.globalFolder = this.normalizeFolderValue(folder);
      // Update all pending files to use the new global folder
      this.files.forEach(fileData => {
        if (fileData.status === 'pending') {
          this.$set(fileData, 'folder', this.globalFolder);
        }
      });
    },
    handleGlobalVisibilityChange(visibility) {
      this.globalVisibility = visibility;
      // Update all pending files to use the new global visibility
      this.files.forEach(fileData => {
        if (fileData.status === 'pending') {
          this.$set(fileData, 'visibility', visibility);
        }
      });
    },
    handleGlobalOverwriteChange() {
      // Update all pending files to use the new global overwrite setting
      this.files.forEach(fileData => {
        if (fileData.status === 'pending') {
          this.$set(fileData, 'overwrite', this.globalOverwrite);
        }
      });
    },
    handleFileUpdate(fileId, updates) {
      const fileData = this.files.find(f => f.id === fileId);
      if (fileData) {
        Object.assign(fileData, updates);
      }
    },
    handleFileRemove(fileId) {
      if (this.uploading) return;

      const index = this.files.findIndex(f => f.id === fileId);
      if (index !== -1) {
        this.files.splice(index, 1);
      }
    },
    clearCompleted() {
      this.files = this.files.filter(f => f.status === 'pending' || f.status === 'uploading');
    },
    async uploadAll() {
      this.uploading = true;

      const pendingFileIds = this.files
        .filter(f => f.status === 'pending')
        .map(f => f.id);
      this.totalFiles = pendingFileIds.length;
      this.currentFileIndex = 0;
      const resources = [];

      for (const fileId of pendingFileIds) {
        const fileData = this.files.find(f => f.id === fileId);
        if (!fileData || fileData.status !== 'pending') {
          continue;
        }

        this.currentFileIndex++;
        await this.uploadSingleFile(fileData, false);

        if (fileData.status === 'success' && fileData.resource && this.files.includes(fileData)) {
          resources.push(fileData.resource);
        }
      }

      this.uploading = false;
      this.currentFileIndex = 0;
      this.totalFiles = 0;

      // Emit every resource that succeeded in this batch, even when siblings
      // failed: those files are already on the remote and must land in the
      // form value, otherwise a retry duplicates them. Failed rows stay in
      // the table for retry. Scoped to this batch so resources emitted by an
      // earlier run are not added twice.
      if (resources.length > 0) {
        resources.forEach(resource => {
          this.$emit('uploaded', resource);
        });

        this.$emit('all-uploaded', resources);
      }
    },
    useExistingResource(fileData) {
      if (this.uploading) return;

      // A 409 response carries the already-existing remote resource; let the
      // user attach it instead of re-uploading with overwrite.
      if (!fileData.resource) return;

      fileData.status = 'success';
      fileData.error = null;
      fileData.progress = 100;

      this.$emit('uploaded', fileData.resource);
      this.$emit('all-uploaded', [fileData.resource]);
    },
    async uploadSingleFile(fileData, emitUpload = true) {
      if (!this.files.includes(fileData) || fileData.status !== 'pending') {
        return;
      }

      fileData.status = 'uploading';
      fileData.progress = 0;
      fileData.error = null;

      const data = new FormData();
      data.append('file', fileData.file);
      data.append('filename', fileData.filename);
      data.append('folder', this.normalizeFolderValue(fileData.folder));
      data.append('overwrite', fileData.overwrite);
      if (fileData.visibility) {
        data.append('visibility', fileData.visibility);
      }
      data.append('hide_filename', this.config.hideFilename);

      for (const [key, value] of Object.entries(this.config.uploadContext)) {
        data.append(`upload_context[${key}]`, value);
      }

      try {
        const response = await axios.post(this.config.paths.upload_resources, data, {
          onUploadProgress: (progressEvent) => {
            fileData.progress = progressEvent.total
              ? Math.round((progressEvent.loaded * 100) / progressEvent.total)
              : 0;
          }
        });

        if (this.config.allowedTypes.length > 0 && this.config.allowedTypes.indexOf(response.data.type) === -1) {
          fileData.status = 'error';
          fileData.error = this.config.translations.upload_error_unsupported_resource_type + this.config.allowedTypes.join(', ');
        } else {
          fileData.status = 'success';
          fileData.resource = response.data;
          fileData.progress = 100;

          if (emitUpload) {
            this.$emit('all-uploaded', [response.data]);
          }
        }
      } catch (error) {
        fileData.status = 'error';
        fileData.progress = 0;

        if (error.response) {
          if (error.response.status === 409) {
            fileData.error = this.config.translations.upload_error_existing_resource;
            fileData.resource = error.response.data; // Store existing resource
          } else {
            fileData.error = error.response.data.detail
              ? error.response.data.detail
              : 'Error ' + error.response.status + ' - ' + error.response.statusText;
          }
        } else {
          fileData.error = error.message || 'Upload failed';
        }
      }
    },
  },
  watch: {
    visibilities: function(newList) {
      if (newList.length === 0) return;

      if (!this.globalVisibility) {
        this.globalVisibility = this.defaultVisibility;
      }

      // Re-align pending files: replace empty or now-disallowed visibilities with the
      // current default. Files whose visibility is in the allowed list are user-valid
      // and must not be overwritten.
      const allowedIds = newList.map(v => v.id);
      this.files.forEach(fileData => {
        if (fileData.status !== 'pending') return;
        if (!fileData.visibility || !allowedIds.includes(fileData.visibility)) {
          fileData.visibility = this.globalVisibility;
        }
      });
    }
  },
  mounted() {
    this.globalVisibility = this.defaultVisibility;
  },
  beforeDestroy() {
    if (this.noticeTimer) clearTimeout(this.noticeTimer);
  }
};
</script>

<style scoped lang="scss">
@import "../scss/_variables";

.multi-upload-inline {
  margin-top: 20px;
}

.global-settings {
  padding: 15px;
  background-color: lighten($netgen-primary, 45%);
  border: 1px solid lighten($netgen-primary, 30%);
  border-radius: 4px;
  margin-bottom: 15px;

  .global-setting-item {
    margin-bottom: 15px;

    &:last-child {
      margin-bottom: 0;
    }

    label {
      font-weight: 600;
      margin-bottom: 8px;
      display: block;
    }
  }

  .global-folder {
    label {
      white-space: nowrap;
    }
  }

  .global-visibility {
    .v-select {
      max-width: 300px;
    }
  }

  .global-overwrite {
    label {
      display: flex;
      align-items: center;
      gap: 8px;
      cursor: pointer;
      font-weight: normal;

      input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
      }
    }
  }
}

.dropzone {
  border: 2px dashed $mercury;
  border-radius: 4px;
  padding: 30px 20px;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s ease;
  background-color: $wild-sand;
  margin-bottom: 20px;

  &:hover {
    border-color: $netgen-primary;
    background-color: lighten($netgen-primary, 45%);
  }

  &.dragover {
    border-color: $netgen-primary;
    background-color: lighten($netgen-primary, 40%);
    transform: scale(1.02);
  }

  &.has-files {
    padding: 20px;
  }
  
  &.disabled {
    opacity: 0.5;
    cursor: not-allowed;
    background-color: #f5f5f5;
    
    &:hover {
      border-color: $mercury;
      background-color: #f5f5f5;
    }
  }

  .dropzone-content {
    pointer-events: none;

    .ng-icon {
      font-size: 48px;
      color: $alto;
      margin-bottom: 10px;
    }

    p {
      margin: 10px 0;
      color: $dusty-gray;
      font-size: 14px;
    }

    button {
      pointer-events: none;
      margin-top: 10px;
    }
  }
}

.overall-progress {
  padding: 15px;
  background-color: lighten($netgen-primary, 45%);
  border: 1px solid lighten($netgen-primary, 30%);
  border-radius: 4px;
  margin-bottom: 15px;

  .progress-info {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
    font-size: 14px;
    font-weight: 600;
    color: darken($netgen-primary, 10%);
  }

  .progress-bar-wrapper {
    width: 100%;
    height: 24px;
    background-color: $white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);

    .progress-bar-fill {
      height: 100%;
      background: linear-gradient(to right, $netgen-primary, lighten($netgen-primary, 10%));
      transition: width 0.3s ease;
      border-radius: 12px;
    }
  }
}

.limit-indicator {
  padding: 12px 16px;
  margin: 15px 0;
  border-radius: 4px;
  font-size: 14px;
  display: flex;
  align-items: center;
  
  i {
    margin-right: 10px;
    font-size: 16px;
  }
  
  &.limit-reached {
    background-color: #fff3cd;
    border: 1px solid #ffc107;
    color: #856404;
  }
  
  &.limit-exceeded {
    background-color: #f8d7da;
    border: 1px solid #dc3545;
    color: #721c24;
    font-weight: 600;
  }
}

.multi-upload-actions {
  padding: 15px;
  background-color: $white;
  box-shadow: inset 1px 0 0 0 $mercury, 0 -1px 0 0 $mercury;
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 20px;

  .upload-summary {
    display: flex;
    gap: 15px;
    font-size: 14px;

    .pending-count {
      color: $dusty-gray;
      font-weight: bold;
    }

    .success-count {
      color: green;
    }

    .error-count {
      color: red;
    }
  }

  button {
    margin-left: 10px;

    &:first-of-type {
      margin-left: auto;
    }
  }
}
</style>
