<template>
  <div class="multi-upload-table">
    <table>
      <thead>
        <tr>
          <th>{{ previewLabel }}</th>
          <th>{{ filenameLabel }}</th>
          <th>{{ folderLabel }}</th>
          <th v-if="showVisibility">{{ visibilityLabel }}</th>
          <th>{{ overwriteLabel }}</th>
          <th>{{ statusLabel }}</th>
          <th>{{ actionsLabel }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="fileData in files"
            :key="fileData.id"
            :class="['status-' + fileData.status, { 'over-limit-row': isOverLimit(fileData) }]">
          <!-- Preview -->
          <td class="preview-cell">
            <div class="file-preview">
              <img v-if="isImage(fileData.file)" :src="getPreviewUrl(fileData)" :alt="fileData.filename">
              <span v-else class="file-icon">
                <i class="fa fa-file" aria-hidden="true"></i>
              </span>
            </div>
          </td>

          <!-- Filename -->
          <td class="filename-cell">
            <input
              type="text"
              :value="fileData.filename"
              :disabled="!isEditable(fileData)"
              :aria-label="filenameLabel + ': ' + fileData.file.name"
              :aria-invalid="isFilenameEmpty(fileData) ? 'true' : null"
              @input="handleFilenameChange(fileData, $event.target.value)"
              @keydown.enter.prevent
              :class="['filename-input', { 'is-invalid': isFilenameEmpty(fileData) }]"
            />
            <div class="file-size">{{ formatByteSize(fileData.file.size) }}</div>
          </td>

          <!-- Folder -->
          <td class="folder-cell">
            <!-- A folder forced by the field configuration cannot be changed per file -->
            <span v-if="config.folder" class="folder-fixed">
              <i class="fa fa-lock" aria-hidden="true"></i>
              {{ config.folder.label || config.folder.id }}
            </span>
            <div
              v-else-if="isEditable(fileData)"
              role="group"
              :aria-label="folderLabel + ': ' + fileData.file.name"
            >
              <folder-tree-select
                :config="config"
                :selected-folder="fileData.folder"
                @change="handleFolderChange(fileData, $event)"
              ></folder-tree-select>
            </div>
            <span v-else>{{ getFolderName(fileData.folder) }}</span>
          </td>

          <!-- Visibility -->
          <td class="visibility-cell" v-if="showVisibility">
            <v-select
              v-if="isEditable(fileData)"
              :input-id="getInputId(fileData, 'visibility')"
              :options="visibilities"
              label="name"
              :value="fileData.visibility"
              :reduce="option => option.id"
              :clearable="false"
              @input="handleVisibilityChange(fileData, $event)"
            />
            <span v-else>{{ getVisibilityName(fileData.visibility) }}</span>
            <label v-if="isEditable(fileData)" class="ngrm-sr-only" :for="getInputId(fileData, 'visibility')">
              {{ visibilityLabel }}: {{ fileData.file.name }}
            </label>
          </td>

          <!-- Overwrite -->
          <td class="overwrite-cell">
            <input
              type="checkbox"
              :checked="fileData.overwrite"
              :disabled="!isEditable(fileData)"
              :aria-label="overwriteLabel + ': ' + fileData.file.name"
              @change="handleOverwriteChange(fileData, $event.target.checked)"
              class="overwrite-checkbox"
            />
          </td>

          <!-- Status -->
          <td class="status-cell">
            <div class="status-indicator">
              <span v-if="fileData.status === 'pending'" class="status-badge status-pending">
                <i class="fa fa-clock-o" aria-hidden="true"></i> {{ pendingLabel }}
              </span>
              <span v-else-if="fileData.status === 'uploading'" class="status-badge status-uploading">
                <i class="fa fa-spinner fa-spin" aria-hidden="true"></i> {{ fileData.progress }}%
              </span>
              <span v-else-if="fileData.status === 'success'" class="status-badge status-success">
                <i class="fa fa-check" aria-hidden="true"></i> {{ successLabel }}
              </span>
              <span v-else-if="fileData.status === 'error'" class="status-badge status-error">
                <i class="fa fa-exclamation-circle" aria-hidden="true"></i> {{ errorLabel }}
              </span>
            </div>
            <div v-if="fileData.status === 'uploading'" class="progress-bar">
              <div class="progress-fill" :style="{ width: fileData.progress + '%' }"></div>
            </div>
            <div v-if="fileData.error" class="error-message">{{ fileData.error }}</div>
            <div v-if="fileData.notice" class="notice-message">{{ fileData.notice }}</div>
          </td>

          <!-- Actions -->
          <td class="actions-cell">
            <button
              v-if="fileData.status === 'pending'"
              type="button"
              class="btn btn-sm btn-primary"
              @click="$emit('upload-single', fileData)"
              :disabled="!canUploadRow(fileData)"
              :title="uploadFileLabel"
              :aria-label="uploadFileLabel + ': ' + fileData.file.name"
            >
              <i class="fa fa-upload" aria-hidden="true"></i>
            </button>
            <button
              v-if="fileData.status === 'error'"
              type="button"
              class="btn btn-sm btn-primary"
              @click="$emit('retry', fileData)"
              :disabled="!canUploadRow(fileData)"
              :aria-label="retryLabel + ': ' + fileData.file.name"
            >
              <i class="fa fa-repeat" aria-hidden="true"></i> {{ retryLabel }}
            </button>
            <button
              v-if="fileData.status === 'error' && fileData.resource"
              type="button"
              class="btn btn-sm btn-default"
              @click="$emit('use-existing', fileData)"
              :disabled="batchUploading"
              :aria-label="useExistingLabel + ': ' + fileData.file.name"
            >
              <i class="fa fa-link" aria-hidden="true"></i> {{ useExistingLabel }}
            </button>
            <button
              type="button"
              class="btn btn-sm btn-danger"
              @click="handleRemove(fileData)"
              :disabled="fileData.status === 'uploading' || batchUploading"
              :title="removeFileLabel"
              :aria-label="removeFileLabel + ': ' + fileData.file.name"
            >
              <i class="fa fa-trash" aria-hidden="true"></i>
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
import { formatByteSize } from "../utility/utility";

export default {
  name: "MultiUploadTable",
  props: {
    files: Array,
    config: Object,
    visibilities: Array,
    // Rows beyond the field's upload limit; they cannot be uploaded until others are removed.
    overLimitIds: {
      type: Array,
      default: () => []
    },
    batchUploading: {
      type: Boolean,
      default: false
    }
  },
  computed: {
    t() {
      return this.config.translations || {};
    },
    showVisibility() {
      return this.visibilities.length > 1;
    },
    previewLabel() {
      return this.t.multi_upload_table_preview || 'Preview';
    },
    filenameLabel() {
      return this.t.multi_upload_table_filename || 'Filename';
    },
    folderLabel() {
      return this.t.multi_upload_table_folder || 'Folder';
    },
    visibilityLabel() {
      return this.t.multi_upload_table_visibility || 'Visibility';
    },
    overwriteLabel() {
      return this.t.upload_checkbox_overwrite || 'Overwrite';
    },
    statusLabel() {
      return this.t.multi_upload_table_status || 'Status';
    },
    actionsLabel() {
      return this.t.multi_upload_table_actions || 'Actions';
    },
    pendingLabel() {
      return this.t.multi_upload_status_pending || 'Pending';
    },
    successLabel() {
      return this.t.multi_upload_status_success || 'Success';
    },
    errorLabel() {
      return this.t.multi_upload_status_error || 'Error';
    },
    uploadFileLabel() {
      return this.t.multi_upload_table_upload_file || 'Upload this file';
    },
    removeFileLabel() {
      return this.t.multi_upload_table_remove_file || 'Remove file';
    },
    useExistingLabel() {
      return this.t.multi_upload_use_existing || 'Use existing file';
    },
    retryLabel() {
      return this.t.multi_upload_retry || 'Retry';
    },
    rootFolderLabel() {
      return this.t.upload_root_folder || '(root)';
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
    formatByteSize,
    // Failed rows stay editable so a 409 can be fixed by renaming or overwriting.
    isEditable(fileData) {
      return !this.batchUploading && (fileData.status === 'pending' || fileData.status === 'error');
    },
    isOverLimit(fileData) {
      return this.overLimitIds.includes(fileData.id);
    },
    isFilenameEmpty(fileData) {
      return (fileData.filename || '').trim() === '';
    },
    canUploadRow(fileData) {
      return !this.batchUploading && !this.isOverLimit(fileData) && !this.isFilenameEmpty(fileData);
    },
    getInputId(fileData, field) {
      return `ngrm-upload-${this._uid}-${fileData.id}-${field}`;
    },
    isImage(file) {
      return file.type.startsWith('image/');
    },
    getPreviewUrl(fileData) {
      if (!this.previewUrls[fileData.id]) {
        this.previewUrls[fileData.id] = URL.createObjectURL(fileData.file);
      }
      return this.previewUrls[fileData.id];
    },
    getFolderName(folder) {
      return folder || this.rootFolderLabel;
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

    &.is-invalid {
      border-color: red;
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

  .folder-fixed {
    color: $dusty-gray;

    i {
      margin-right: 4px;
    }
  }
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

  .notice-message {
    margin-top: 4px;
    font-size: 11px;
    color: $dusty-gray;
  }
}

.actions-cell {
  min-width: 100px;
  text-align: center;
  white-space: nowrap;

  .btn {
    padding: 6px 10px;
    font-size: 12px;
    margin: 0 2px;
  }
}
</style>
