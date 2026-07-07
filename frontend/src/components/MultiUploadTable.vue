<template>
  <div class="multi-upload-table">
    <table>
      <thead>
        <tr>
          <th>{{ previewLabel }}</th>
          <th>{{ filenameLabel }}</th>
          <th>{{ folderLabel }}</th>
          <th v-if="visibilities.length > 0">{{ visibilityLabel }}</th>
          <th>{{ overwriteLabel }}</th>
          <th>{{ statusLabel }}</th>
          <th>{{ actionsLabel }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(fileData, index) in files" 
            :key="fileData.id" 
            :class="['status-' + fileData.status, { 'over-limit-row': isOverLimit && uploadLimit > 0 && index >= uploadLimit }]">
          <!-- Preview -->
          <td class="preview-cell">
            <div class="file-preview">
              <img v-if="isImage(fileData.file)" :src="getPreviewUrl(fileData)" :alt="fileData.filename">
              <span v-else class="file-icon">
                <i class="fa fa-file"></i>
              </span>
            </div>
          </td>

          <!-- Filename -->
          <td class="filename-cell">
            <input
              type="text"
              :value="fileData.filename"
              :disabled="fileData.status !== 'pending' || batchUploading"
              @input="handleFilenameChange(fileData, $event.target.value)"
              class="filename-input"
            />
            <div class="file-size">{{ formatFileSize(fileData.file.size) }}</div>
          </td>

          <!-- Folder -->
          <td class="folder-cell">
            <folder-tree-select
              v-if="fileData.status === 'pending'"
              :config="config"
              :selected-folder="fileData.folder"
              @change="handleFolderChange(fileData, $event)"
            ></folder-tree-select>
            <span v-else>{{ fileData.folder || '/' }}</span>
          </td>

          <!-- Visibility -->
          <td class="visibility-cell" v-if="visibilities.length > 0">
            <v-select
              v-if="fileData.status === 'pending'"
              :options="visibilities"
              label="name"
              :value="fileData.visibility"
              :reduce="option => option.id"
              :clearable="false"
              @input="handleVisibilityChange(fileData, $event)"
            />
            <span v-else>{{ getVisibilityName(fileData.visibility) }}</span>
          </td>

          <!-- Overwrite -->
          <td class="overwrite-cell">
            <input
              type="checkbox"
              :checked="fileData.overwrite"
              :disabled="fileData.status !== 'pending' || batchUploading"
              @change="handleOverwriteChange(fileData, $event.target.checked)"
              class="overwrite-checkbox"
            />
          </td>

          <!-- Status -->
          <td class="status-cell">
            <div class="status-indicator">
              <span v-if="fileData.status === 'pending'" class="status-badge status-pending">
                <i class="fa fa-clock-o"></i> {{ pendingLabel }}
              </span>
              <span v-else-if="fileData.status === 'uploading'" class="status-badge status-uploading">
                <i class="fa fa-spinner fa-spin"></i> {{ fileData.progress }}%
              </span>
              <span v-else-if="fileData.status === 'success'" class="status-badge status-success">
                <i class="fa fa-check"></i> {{ successLabel }}
              </span>
              <span v-else-if="fileData.status === 'error'" class="status-badge status-error">
                <i class="fa fa-exclamation-circle"></i> {{ errorLabel }}
              </span>
            </div>
            <div v-if="fileData.status === 'uploading'" class="progress-bar">
              <div class="progress-fill" :style="{ width: fileData.progress + '%' }"></div>
            </div>
            <div v-if="fileData.error" class="error-message">{{ fileData.error }}</div>
          </td>

          <!-- Actions -->
          <td class="actions-cell">
            <button
              v-if="fileData.status === 'pending'"
              type="button"
              class="btn btn-sm btn-primary"
              @click="handleUpload(fileData)"
              :disabled="batchUploading"
              :title="uploadFileLabel"
            >
              <i class="fa fa-upload"></i>
            </button>
            <button
              v-if="fileData.status === 'error' && fileData.resource"
              type="button"
              class="btn btn-sm btn-default"
              @click="handleUseExisting(fileData)"
              :disabled="batchUploading"
              :title="useExistingLabel"
            >
              <i class="fa fa-link"></i> {{ useExistingLabel }}
            </button>
            <button
              type="button"
              class="btn btn-sm btn-danger"
              @click="handleRemove(fileData)"
              :disabled="fileData.status === 'uploading' || batchUploading"
              :title="removeFileLabel"
            >
              <i class="fa fa-trash"></i>
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import FolderTreeSelect from "./FolderTreeSelect";
import vSelect from "vue-select";

export default {
  name: "MultiUploadTable",
  props: {
    files: Array,
    config: Object,
    visibilities: Array,
    isOverLimit: {
      type: Boolean,
      default: false
    },
    uploadLimit: {
      type: Number,
      default: 0
    },
    batchUploading: {
      type: Boolean,
      default: false
    }
  },
  computed: {
    previewLabel() {
      return this.config.translations.multi_upload_table_preview || 'Preview';
    },
    filenameLabel() {
      return this.config.translations.multi_upload_table_filename || 'Filename';
    },
    folderLabel() {
      return this.config.translations.multi_upload_table_folder || 'Folder';
    },
    visibilityLabel() {
      return this.config.translations.multi_upload_table_visibility || 'Visibility';
    },
    overwriteLabel() {
      return this.config.translations.upload_checkbox_overwrite || 'Overwrite';
    },
    statusLabel() {
      return this.config.translations.multi_upload_table_status || 'Status';
    },
    actionsLabel() {
      return this.config.translations.multi_upload_table_actions || 'Actions';
    },
    pendingLabel() {
      return this.config.translations.multi_upload_status_pending || 'Pending';
    },
    successLabel() {
      return this.config.translations.multi_upload_status_success || 'Success';
    },
    errorLabel() {
      return this.config.translations.multi_upload_status_error || 'Error';
    },
    uploadFileLabel() {
      return this.config.translations.multi_upload_table_upload_file || 'Upload this file';
    },
    removeFileLabel() {
      return this.config.translations.multi_upload_table_remove_file || 'Remove file';
    },
    useExistingLabel() {
      return this.config.translations.multi_upload_use_existing || 'Use existing file';
    },
  },
  components: {
    "folder-tree-select": FolderTreeSelect,
    "v-select": vSelect
  },
  data() {
    return {
      previewUrls: {}
    };
  },
  methods: {
    isImage(file) {
      return file.type.startsWith('image/');
    },
    getPreviewUrl(fileData) {
      if (!this.previewUrls[fileData.id]) {
        this.previewUrls[fileData.id] = URL.createObjectURL(fileData.file);
      }
      return this.previewUrls[fileData.id];
    },
    formatFileSize(bytes) {
      if (bytes === 0) return '0 Bytes';
      const k = 1024;
      const sizes = ['Bytes', 'KB', 'MB', 'GB'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    },
    getVisibilityName(visibilityId) {
      const visibility = this.visibilities.find(v => v.id === visibilityId);
      return visibility ? visibility.name : visibilityId;
    },
    handleFilenameChange(fileData, filename) {
      this.$emit('update-file', fileData.id, { filename });
    },
    handleFolderChange(fileData, folder) {
      this.$emit('update-file', fileData.id, { folder: folder });
    },
    handleVisibilityChange(fileData, visibility) {
      this.$emit('update-file', fileData.id, { visibility });
    },
    handleOverwriteChange(fileData, overwrite) {
      this.$emit('update-file', fileData.id, { overwrite });
    },
    handleUpload(fileData) {
      this.$emit('upload-single', fileData);
    },
    handleUseExisting(fileData) {
      this.$emit('use-existing', fileData);
    },
    handleRemove(fileData) {
      // Revoke preview URL if exists
      if (this.previewUrls[fileData.id]) {
        URL.revokeObjectURL(this.previewUrls[fileData.id]);
        delete this.previewUrls[fileData.id];
      }
      this.$emit('remove-file', fileData.id);
    }
  },
  beforeDestroy() {
    // Cleanup all preview URLs
    Object.values(this.previewUrls).forEach(url => {
      URL.revokeObjectURL(url);
    });
  }
};
</script>

<style scoped lang="scss">
@import "../scss/_variables";

.multi-upload-table {
  margin: 20px 0;
  overflow-x: auto;

  table {
    width: 100%;
    border-collapse: collapse;
    background-color: $white;

    thead {
      background-color: $mercury;

      th {
        padding: 12px 8px;
        text-align: left;
        font-weight: 600;
        border-bottom: 2px solid darken($mercury, 10%);
        font-size: 13px;
      }
    }

    tbody {
      tr {
        border-bottom: 1px solid $mercury;
        transition: background-color 0.2s;

        &:hover {
          background-color: lighten($mercury, 5%);
        }

        &.status-uploading {
          background-color: lighten($netgen-primary, 45%);
        }

        &.status-success {
          background-color: lighten(green, 60%);
        }

        &.status-error {
          background-color: lighten(red, 45%);
        }
        
        &.over-limit-row {
          background-color: #f8d7da;
          border-left: 4px solid #dc3545;
          
          td {
            color: #721c24;
          }
        }
      }

      td {
        padding: 10px 8px;
        vertical-align: middle;
      }
    }
  }
}

.preview-cell {
  width: 80px;

  .file-preview {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid $mercury;
    border-radius: 4px;
    overflow: hidden;
    background-color: $wild-sand;

    img {
      max-width: 100%;
      max-height: 100%;
      object-fit: cover;
    }

    .file-icon {
      font-size: 24px;
      color: $alto;
    }
  }
}

.filename-cell {
  min-width: 200px;

  .filename-input {
    width: 100%;
    padding: 6px 8px;
    border: 1px solid $mercury;
    border-radius: 3px;
    font-size: 13px;

    &:disabled {
      background-color: $wild-sand;
      cursor: not-allowed;
    }
  }

  .file-size {
    margin-top: 4px;
    font-size: 11px;
    color: $dusty-gray;
  }
}

.folder-cell {
  min-width: 150px;
}

.overwrite-cell {
  width: 80px;
  text-align: center;

  .overwrite-checkbox {
    width: 18px;
    height: 18px;
    cursor: pointer;

    &:disabled {
      cursor: not-allowed;
    }
  }
}

.visibility-cell {
  min-width: 120px;

  .v-select {
    font-size: 13px;
  }
}

.status-cell {
  min-width: 150px;

  .status-indicator {
    margin-bottom: 5px;
  }

  .status-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 500;

    &.status-pending {
      background-color: lighten($dusty-gray, 40%);
      color: darken($dusty-gray, 20%);
    }

    &.status-uploading {
      background-color: lighten($netgen-primary, 40%);
      color: darken($netgen-primary, 20%);
    }

    &.status-success {
      background-color: lighten(green, 50%);
      color: darken(green, 20%);
    }

    &.status-error {
      background-color: lighten(red, 40%);
      color: darken(red, 20%);
    }
  }

  .progress-bar {
    width: 100%;
    height: 6px;
    background-color: $mercury;
    border-radius: 3px;
    overflow: hidden;
    margin-top: 4px;

    .progress-fill {
      height: 100%;
      background-color: $netgen-primary;
      transition: width 0.3s ease;
    }
  }

  .error-message {
    margin-top: 4px;
    font-size: 11px;
    color: red;
    word-break: break-word;
  }
}

.actions-cell {
  width: 100px;
  text-align: center;

  .btn {
    padding: 6px 10px;
    font-size: 12px;
    margin: 0 2px;
  }
}
</style>
