<template>
  <div class="multi-upload-inline">
    <!-- Shown when a form submit was blocked because files are not uploaded yet -->
    <div
      v-if="submitBlockedMessage"
      ref="submitBlocked"
      class="limit-indicator limit-exceeded submit-blocked"
      role="alert"
    >
      <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
      <span>{{ submitBlockedMessage }}</span>
    </div>

    <!-- Dropzone for drag & drop -->
    <div
      class="dropzone"
      :class="{ 'dragover': isDragOver, 'has-files': files.length > 0, 'disabled': isDropzoneDisabled }"
      @drop.prevent="handleDrop"
      @dragover.prevent="!isDropzoneDisabled && (isDragOver = true)"
      @dragleave.prevent="isDragOver = false"
      @click="!isDropzoneDisabled && triggerFileInput()"
    >
      <div class="dropzone-content">
        <i class="ng-icon ng-upload-cloud" aria-hidden="true"></i>
        <p>{{ dropZoneLabel }}</p>
        <button type="button"
                class="btn btn-blue"
                :disabled="isDropzoneDisabled"
                @click.stop="triggerFileInput">
          {{ browseLabel }}
        </button>
      </div>
      <input
        ref="fileInput"
        type="file"
        :multiple="!isSingleFileMode"
        :accept="acceptedFileTypes"
        hidden
        :disabled="isDropzoneDisabled"
        @change="handleFileSelect"
      />
    </div>

    <!-- Limit indicator (only show when there are queued files or we're over limit) -->
    <div v-if="hasLimit && !isSingleFileMode && (pendingFilesCount > 0 || isOverLimit)"
         class="limit-indicator"
         :class="limitClass">
      <i v-if="isOverLimit" class="fa fa-exclamation-triangle" aria-hidden="true"></i>
      <i v-else-if="isAtLimit" class="fa fa-info-circle" aria-hidden="true"></i>
      <i v-else class="fa fa-check-circle" aria-hidden="true"></i>
      <span>{{ limitMessage }}</span>
    </div>

    <!-- Transient banner for blocked/clamped selection attempts -->
    <div v-if="notice"
         class="limit-indicator"
         :class="notice.severity === 'error' ? 'limit-exceeded' : 'limit-reached'"
         role="alert">
      <i :class="notice.severity === 'error' ? 'fa fa-exclamation-triangle' : 'fa fa-info-circle'" aria-hidden="true"></i>
      <span>{{ notice.message }}</span>
    </div>

    <!-- Global settings (shown when files are added) -->
    <div v-if="files.length > 0" class="global-settings">
      <!-- Global folder selector (hidden when the field forces a folder) -->
      <div v-if="!config.folder" class="global-setting-item global-folder">
        <label>{{ globalFolderLabel }}</label>
        <folder-tree-select
          :config="config"
          :selected-folder="effectiveGlobalFolder"
          @change="handleGlobalFolderChange"
        ></folder-tree-select>
      </div>

      <!-- Global visibility selector, only when there is a choice -->
      <div v-if="visibilities.length > 1" class="global-setting-item global-visibility">
        <label :for="globalVisibilityInputId">{{ globalVisibilityLabel }}</label>
        <v-select
          :input-id="globalVisibilityInputId"
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
          {{ uploadOverwriteLabel }} ({{ applyToAllLabel }})
        </label>
      </div>
    </div>

    <!-- File list table -->
    <multi-upload-table
      v-if="files.length > 0"
      :files="files"
      :config="config"
      :visibilities="visibilities"
      :over-limit-ids="overLimitIds"
      :batch-uploading="uploading"
      @update-file="handleFileUpdate"
      @remove-file="handleFileRemove"
      @upload-single="uploadRow"
      @retry="uploadRow"
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
        :disabled="uploading || !canUpload()"
        @click="uploadAll"
      >
        {{ uploading ? uploadingLabel : uploadAllLabel }}
      </button>
      <button
        type="button"
        class="btn btn-default"
        :disabled="uploading || successCount === 0"
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

// An upload is cancelled when nothing happens for this long: no upload progress
// and no response. A slow but moving upload of a big file is never cut off.
const UPLOAD_IDLE_TIMEOUT_MS = 180000;
const UPLOAD_TIMEOUT_REASON = 'ngrm-upload-timeout';

// Same name, size and modification time: treated as the same file picked twice.
const fileKey = (file) => `${file.name}/${file.size}/${file.lastModified}`;

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
    },
    // Remote ids already in the field, to tell the user when an upload adds nothing new.
    existingIds: {
      type: Array,
      default: () => []
    },
    fieldId: {
      type: String,
      default: ''
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
      files: [], // Array of {id, key, file, filename, folder, visibility, overwrite, status, progress, error, notice, resource}
      nextId: 1,
      globalFolder: '', // '' = not chosen yet, see effectiveGlobalFolder
      globalVisibility: '',
      globalOverwrite: false,
      currentFileIndex: 0,
      totalFiles: 0,
      batchCompleted: 0,
      currentBatchFile: null,
      notice: null, // {message, severity: 'info'|'error'} — transient banner replacing alert()
      noticeTimer: null,
      submitBlockedMessage: null,
    };
  },
  computed: {
    t() {
      return this.config.translations || {};
    },
    isSingleFileMode() {
      return this.uploadLimit === 1;
    },
    // Uploads start in the configured parent folder. Read lazily: the config is
    // filled in only after this component has been created.
    effectiveGlobalFolder() {
      if (this.globalFolder) return this.globalFolder;
      return this.config.parentFolder ? this.config.parentFolder.id : '';
    },
    globalVisibilityInputId() {
      return `ngrm-${this.fieldId}-upload-visibility`;
    },
    dropZoneLabel() {
      if (this.isSingleFileMode) {
        return this.t.multi_upload_drop_zone_single || 'Drop file here or click to browse';
      }
      return this.t.multi_upload_drop_zone || 'Drop files here or click to browse';
    },
    browseLabel() {
      if (this.isSingleFileMode) {
        return this.t.multi_upload_browse_single || 'Browse file';
      }
      return this.t.multi_upload_browse || 'Browse files';
    },
    globalFolderLabel() {
      return this.t.multi_upload_global_folder || 'Default folder for all files:';
    },
    globalVisibilityLabel() {
      return this.t.multi_upload_global_visibility || 'Default visibility for all files:';
    },
    applyToAllLabel() {
      return this.t.multi_upload_global_apply_to_all || 'apply to all files';
    },
    uploadOverwriteLabel() {
      return this.t.upload_checkbox_overwrite || 'Overwrite';
    },
    filesPendingLabel() {
      return this.t.multi_upload_files_pending || 'pending';
    },
    filesSuccessLabel() {
      return this.t.multi_upload_files_success || 'uploaded';
    },
    filesErrorLabel() {
      return this.t.multi_upload_files_error || 'failed';
    },
    uploadingLabel() {
      return this.t.multi_upload_uploading || 'Uploading...';
    },
    uploadAllLabel() {
      return this.t.multi_upload_button_upload_all || 'Upload all';
    },
    clearCompletedLabel() {
      return this.t.multi_upload_clear_completed || 'Clear completed';
    },
    hasLimit() {
      return this.uploadLimit > 0;
    },
    // Rows that still take a slot: not uploaded yet, uploading, or failed (they can be retried).
    queuedFiles() {
      return this.files.filter(f => f.status === 'pending' || f.status === 'uploading' || f.status === 'error');
    },
    pendingFilesCount() {
      return this.queuedFiles.length;
    },
    // With a limit of 1 an upload replaces the current item, so it takes no slot.
    occupiedCount() {
      return this.isSingleFileMode ? 0 : this.currentCount;
    },
    queueCapacity() {
      if (!this.hasLimit) return Infinity;
      return Math.max(0, this.uploadLimit - this.occupiedCount);
    },
    overLimitIds() {
      if (!this.hasLimit) return [];
      return this.queuedFiles.slice(this.queueCapacity).map(f => f.id);
    },
    remainingSlots() {
      if (!this.hasLimit) return Infinity;
      return Math.max(0, this.uploadLimit - this.totalCount);
    },
    totalCount() {
      return this.occupiedCount + this.pendingFilesCount;
    },
    isAtLimit() {
      return this.hasLimit && this.totalCount >= this.uploadLimit;
    },
    isOverLimit() {
      return this.hasLimit && this.totalCount > this.uploadLimit;
    },
    isDropzoneDisabled() {
      if (this.isSingleFileMode) {
        return this.uploading;
      }

      return this.isAtLimit;
    },
    hasUnfinishedFiles() {
      return this.files.some(f => f.status === 'pending' || f.status === 'uploading');
    },
    limitMessage() {
      if (!this.hasLimit) return '';
      if (this.isOverLimit) {
        return (this.t.limit_exceeded || 'Too many files! Maximum %limit% allowed')
          .replace('%limit%', this.uploadLimit);
      }
      if (this.isAtLimit) {
        return (this.t.limit_reached || 'File limit reached (%limit% maximum)')
          .replace('%limit%', this.uploadLimit);
      }
      return (this.t.limit_remaining || '%remaining% slot(s) remaining')
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
    // Progress of the current batch only: finished files plus the share of the file in flight.
    overallProgress() {
      if (this.totalFiles === 0) return 0;
      const current = this.currentBatchFile && this.currentBatchFile.status === 'uploading'
        ? (this.currentBatchFile.progress || 0) / 100
        : 0;
      const progress = (this.batchCompleted + current) / this.totalFiles * 100;
      return Math.min(100, Math.round(progress));
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
    showLimitReachedNotice() {
      this.showNotice(
        (this.t.limit_reached || 'File limit reached (%limit% maximum)').replace('%limit%', this.uploadLimit),
        'error'
      );
    },
    triggerFileInput() {
      this.$refs.fileInput.click();
    },
    handleFileSelect(event) {
      this.addFiles(Array.from(event.target.files));
      // Reset input so same file can be selected again
      event.target.value = '';
    },
    handleDrop(event) {
      this.isDragOver = false;
      // At the limit addFiles explains why nothing was added; only an upload in
      // progress in single file mode ignores the drop.
      if (this.isSingleFileMode && this.uploading) return;
      this.addFiles(Array.from(event.dataTransfer.files));
    },
    // Drops files that are already in the list, and repeats within the same selection.
    filterDuplicateFiles(files) {
      const knownKeys = new Set(this.files.map(f => f.key));
      const duplicates = [];
      const unique = files.filter((file) => {
        const key = fileKey(file);
        if (knownKeys.has(key)) {
          duplicates.push(file.name);
          return false;
        }
        knownKeys.add(key);
        return true;
      });

      if (duplicates.length > 0) {
        this.showNotice(
          (this.t.multi_upload_duplicate_file || '"%filename%" is already in the upload list')
            .replace('%filename%', [...new Set(duplicates)].join('", "')),
          'info'
        );
      }

      return unique;
    },
    addFiles(files) {
      let filesArray = this.filterDuplicateFiles(Array.from(files));
      if (filesArray.length === 0) return;

      if (this.isSingleFileMode) {
        // One file replaces whatever is waiting in the list; the upload then
        // replaces the item in the field.
        this.files = this.files.filter(f => f.status === 'uploading');
        filesArray = [filesArray[0]];
      } else if (this.hasLimit) {
        const availableSlots = this.queueCapacity - this.pendingFilesCount;
        if (availableSlots <= 0) {
          this.showLimitReachedNotice();
          return;
        }
        if (filesArray.length > availableSlots) {
          this.showNotice(
            (this.t.limit_remaining || '%remaining% slot(s) remaining').replace('%remaining%', availableSlots).replace('%limit%', this.uploadLimit),
            'info'
          );
          filesArray.splice(availableSlots);
        }
      }

      filesArray.forEach(file => {
        this.files.push({
          id: this.nextId++,
          key: fileKey(file),
          file: file,
          filename: file.name,
          folder: this.config.folder ? this.config.folder.id : this.effectiveGlobalFolder,
          visibility: this.defaultVisibility,
          overwrite: this.globalOverwrite,
          status: 'pending', // pending, uploading, success, error
          progress: 0,
          error: null,
          notice: null,
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
          this.$set(fileData, 'folder', this.effectiveGlobalFolder);
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
      const index = this.files.findIndex(f => f.id === fileId);
      if (index !== -1 && this.files[index].status !== 'uploading') {
        this.files.splice(index, 1);
      }
    },
    // Failed rows stay: they can still be retried or, after a 409, attached as they are.
    clearCompleted() {
      this.files = this.files.filter(f => f.status !== 'success');
    },
    isInField(resource) {
      const id = resource && (resource.remoteId || resource.remote_id || resource.id);
      return !!id && this.existingIds.includes(id);
    },
    markSucceeded(fileData, resource) {
      fileData.status = 'success';
      fileData.error = null;
      fileData.progress = 100;
      fileData.resource = resource;
      fileData.notice = this.isInField(resource)
        ? (this.t.multi_upload_already_added || 'Already added to this field')
        : null;
    },
    // Whether one more resource fits, counting the ones this batch already uploaded.
    hasRoomFor(alreadyUploaded = 0) {
      if (!this.hasLimit || this.isSingleFileMode) return true;
      return this.currentCount + alreadyUploaded < this.uploadLimit;
    },
    async uploadRow(fileData) {
      if (this.uploading) return;

      if (this.overLimitIds.includes(fileData.id) || !this.hasRoomFor()) {
        this.showLimitReachedNotice();
        return;
      }

      const resource = await this.uploadSingleFile(fileData);
      if (resource && !this.isInField(resource)) {
        this.$emit('all-uploaded', [resource]);
      }
    },
    async uploadAll() {
      if (this.uploading) return;

      const resources = [];
      this.uploading = true;

      try {
        const pendingFileIds = this.files
          .filter(f => f.status === 'pending' && !this.overLimitIds.includes(f.id))
          .map(f => f.id);
        this.totalFiles = pendingFileIds.length;
        this.currentFileIndex = 0;
        this.batchCompleted = 0;

        for (const fileId of pendingFileIds) {
          const fileData = this.files.find(f => f.id === fileId);

          if (fileData && fileData.status === 'pending') {
            if (!this.hasRoomFor(resources.length)) {
              this.showLimitReachedNotice();
              break;
            }

            this.currentFileIndex++;
            this.currentBatchFile = fileData;

            const resource = await this.uploadSingleFile(fileData);
            if (resource && this.files.includes(fileData) && !this.isInField(resource)) {
              resources.push(resource);
            }
          }

          this.batchCompleted++;
        }
      } finally {
        this.uploading = false;
        this.currentFileIndex = 0;
        this.totalFiles = 0;
        this.batchCompleted = 0;
        this.currentBatchFile = null;

        // Emit every resource that succeeded in this batch, even when siblings
        // failed: those files are already on the remote and must land in the
        // form value, otherwise a retry duplicates them. Failed rows stay in
        // the table for retry. Scoped to this batch so resources emitted by an
        // earlier run are not added twice.
        if (resources.length > 0) {
          this.$emit('all-uploaded', resources);
        }
      }
    },
    useExistingResource(fileData) {
      if (this.uploading) return;

      // A 409 response carries the already-existing remote resource; let the
      // user attach it instead of re-uploading with overwrite.
      if (!fileData.resource) return;

      const alreadyInField = this.isInField(fileData.resource);
      if (!alreadyInField && !this.hasRoomFor()) {
        this.showLimitReachedNotice();
        return;
      }

      this.markSucceeded(fileData, fileData.resource);

      if (!alreadyInField) {
        this.$emit('all-uploaded', [fileData.resource]);
      }
    },
    serverErrorMessage(response) {
      const data = response.data;

      if (data && typeof data === 'object') {
        return data.detail || data.message || data.title || null;
      }

      return null;
    },
    describeUploadError(error, fileData) {
      if (axios.isCancel(error)) {
        return this.t.multi_upload_error_timeout || 'The upload timed out. Try again.';
      }

      if (error.response) {
        if (error.response.status === 409) {
          const folder = this.normalizeFolderValue(fileData.folder);
          // Store the existing resource so it can be attached with "use existing".
          fileData.resource = error.response.data;

          return this.config.folderScopedUploads && folder && this.t.upload_error_existing_resource_in_folder
            ? this.t.upload_error_existing_resource_in_folder.replace('%folder%', folder)
            : this.t.upload_error_existing_resource;
        }

        return this.serverErrorMessage(error.response)
          || `Error ${error.response.status} - ${error.response.statusText}`;
      }

      return error.message || this.t.multi_upload_error_failed || 'Upload failed';
    },
    // Uploads one row. Returns the uploaded resource, or null when the row failed.
    async uploadSingleFile(fileData) {
      if (!this.files.includes(fileData) || (fileData.status !== 'pending' && fileData.status !== 'error')) {
        return null;
      }

      const filename = (fileData.filename || '').trim();
      if (filename === '') {
        fileData.status = 'error';
        fileData.error = this.t.multi_upload_error_empty_filename || 'Enter a file name';
        return null;
      }

      fileData.status = 'uploading';
      fileData.progress = 0;
      fileData.error = null;
      fileData.notice = null;
      fileData.resource = null;

      const cancelSource = axios.CancelToken.source();
      let idleTimer = null;
      const restartIdleTimer = () => {
        clearTimeout(idleTimer);
        idleTimer = setTimeout(() => cancelSource.cancel(UPLOAD_TIMEOUT_REASON), UPLOAD_IDLE_TIMEOUT_MS);
      };

      try {
        const data = new FormData();
        data.append('file', fileData.file);
        data.append('filename', filename);
        data.append('folder', this.normalizeFolderValue(fileData.folder));
        data.append('overwrite', fileData.overwrite);
        if (fileData.visibility) {
          data.append('visibility', fileData.visibility);
        }
        data.append('hide_filename', this.config.hideFilename);

        for (const [key, value] of Object.entries(this.config.uploadContext || {})) {
          data.append(`upload_context[${key}]`, value);
        }

        restartIdleTimer();
        const response = await axios.post(this.config.paths.upload_resources, data, {
          cancelToken: cancelSource.token,
          headers: { Accept: 'application/json' },
          onUploadProgress: (progressEvent) => {
            restartIdleTimer();
            fileData.progress = progressEvent.total
              ? Math.min(100, Math.round((progressEvent.loaded * 100) / progressEvent.total))
              : 0;
          }
        });

        const allowedTypes = this.config.allowedTypes || [];
        if (allowedTypes.length > 0 && allowedTypes.indexOf(response.data.type) === -1) {
          fileData.status = 'error';
          fileData.progress = 0;
          fileData.error = this.t.upload_error_unsupported_resource_type + allowedTypes.join(', ');
          return null;
        }

        this.markSucceeded(fileData, response.data);
        return response.data;
      } catch (error) {
        fileData.status = 'error';
        fileData.progress = 0;
        fileData.error = this.describeUploadError(error, fileData);
        return null;
      } finally {
        clearTimeout(idleTimer);
      }
    },
    // Unfinished uploads would end up in the remote storage but not in the saved
    // value, so the form waits until every row is uploaded or removed.
    handleFormSubmit(event) {
      const form = event.target;
      if (!this.hasUnfinishedFiles || !this.$el || !(form instanceof HTMLFormElement) || !form.contains(this.$el)) {
        return;
      }

      event.preventDefault();
      event.stopImmediatePropagation();

      this.submitBlockedMessage = this.files.some(f => f.status === 'uploading')
        ? (this.t.multi_upload_submit_blocked_uploading || 'Wait until the upload has finished before saving.')
        : (this.t.multi_upload_submit_blocked_pending || 'Some files are not uploaded yet. Upload or remove them before saving.');

      this.$nextTick(() => {
        if (this.$refs.submitBlocked) {
          this.$refs.submitBlocked.scrollIntoView({ block: 'center' });
        }
      });
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
    },
    hasUnfinishedFiles(value) {
      if (!value) {
        this.submitBlockedMessage = null;
      }
    },
    pendingFilesCount: {
      immediate: true,
      handler(count) {
        this.$emit('queue-change', count);
      }
    },
  },
  mounted() {
    this.globalVisibility = this.defaultVisibility;
    // Capture phase on the document runs before any submit handler of the form itself.
    this.formSubmitListener = (event) => this.handleFormSubmit(event);
    document.addEventListener('submit', this.formSubmitListener, true);
  },
  beforeDestroy() {
    if (this.noticeTimer) clearTimeout(this.noticeTimer);
    document.removeEventListener('submit', this.formSubmitListener, true);
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
