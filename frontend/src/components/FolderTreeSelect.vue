<template>
  <div class="folder-tree-select" v-click-outside="handleClickOutside">
    <treeselect
      :multiple="false"
      :options="folders"
      :load-options="loadSubFolders"
      v-model="internalSelectedFolder"
      @input="handleFolderChange"
      :placeholder="config.translations.upload_root_folder"
      :clearable="true"
      :defaultExpandLevel="1"
      :appendToBody="true"
      :zIndex="9999"
      :normalizer="normalizeNode"
    >
      <div slot="option-label" slot-scope="{ node }" class="folder-option">
        <span v-if="!node.raw.isCreateAction" class="folder-label">
          {{ node.label }}
        </span>
        <span v-else class="create-folder-action" @click.stop.prevent="showCreateFolder(node.raw.parentNode)">
          <i class="fa fa-plus"></i> {{ config.translations.upload_button_create }}
        </span>
      </div>
      <div slot="value-label" slot-scope="{ node }">
        {{ getFullPath(node.id) }}
      </div>
    </treeselect>

    <!-- Create folder modal/inline input -->
    <div v-if="creatingFolder" class="create-folder-inline">
      <div class="create-folder-header">
        <i class="fa fa-folder"></i>
        <span>{{ config.translations.upload_button_create }} in: {{ creatingFolderParent ? creatingFolderParent.label : '(root)' }}</span>
      </div>
      <div class="create-folder-body">
        <input
          type="text"
          v-model="newFolderName"
          :placeholder="config.translations.upload_placeholder_new_folder"
          @keyup.enter="createFolder"
          @keyup.esc="cancelCreateFolder"
          ref="newFolderInput"
          class="new-folder-input"
        />
        <div class="create-folder-actions">
          <button type="button" class="btn btn-sm btn-primary" @click="createFolder" :disabled="!newFolderName">
            <i class="fa fa-check"></i> {{ config.translations.upload_button_create }}
          </button>
          <button type="button" class="btn btn-sm btn-default" @click="cancelCreateFolder">
            <i class="fa fa-times"></i> {{ config.translations.upload_button_cancel }}
          </button>
        </div>
      </div>
      <div v-if="createFolderError" class="create-folder-error">
        {{ createFolderError }}
      </div>
    </div>
  </div>
</template>

<script>
import Treeselect from '@riophae/vue-treeselect';
import '@riophae/vue-treeselect/dist/vue-treeselect.css'
import { encodeQueryData } from "@/utility/utility";
import { FOLDER_ROOT } from "../constants/facets";
import axios from 'axios';

export default {
  name: "FolderTreeSelect",
  props: {
    config: {
      type: Object,
      required: true
    },
    selectedFolder: {
      type: String,
      default: null
    }
  },
  directives: {
    'click-outside': {
      bind(el, binding) {
        el.clickOutsideEvent = function(event) {
          if (!(el === event.target || el.contains(event.target))) {
            binding.value(event);
          }
        };
        document.body.addEventListener('click', el.clickOutsideEvent);
      },
      unbind(el) {
        document.body.removeEventListener('click', el.clickOutsideEvent);
      }
    }
  },
  components: {
    Treeselect
  },
  data() {
    const rootId = this.config.parentFolder ? this.config.parentFolder.id : FOLDER_ROOT;
    const rootLabel = this.config.parentFolder ? this.config.parentFolder.label : FOLDER_ROOT;

    return {
      folders: [{
        id: rootId,
        label: rootLabel,
        children: null
      }],
      internalSelectedFolder: this.selectedFolder || rootId,
      creatingFolder: false,
      creatingFolderParent: null,
      newFolderName: '',
      createFolderError: null
    };
  },
  methods: {
    getFullPath(folderId) {
      // Handle root folder
      if (!folderId || folderId === FOLDER_ROOT || folderId === '(root)') {
        return this.config.translations.upload_root_folder || '(root)';
      }

      // Replace slashes with " / " for better readability (breadcrumb style)
      return folderId.replace(/\//g, ' / ');
    },
    normalizeNode(node) {
      // Don't normalize if it's the create action
      if (node.isCreateAction) {
        return {
          id: node.id,
          label: node.label,
          // Don't mark as disabled so it can be interacted with
        };
      }
      return {
        id: node.id,
        label: node.label,
        children: node.children,
      };
    },
    handleClickOutside() {
      if (this.creatingFolder) {
        this.cancelCreateFolder();
      }
    },
    handleFolderChange(value) {
      // Check if user selected the "Create new folder" action
      if (value && value.startsWith('__create_')) {
        // Extract parent node ID from the action ID
        const parentId = value.replace('__create_', '');

        // Find the parent node
        const parentNode = this.findNodeById(this.folders, parentId);
        if (parentNode) {
          this.showCreateFolder(parentNode);
        }

        // Don't change the selected folder value
        return;
      }

      this.internalSelectedFolder = value;
      if (typeof value === 'undefined' || !value) {
        this.internalSelectedFolder = this.config.parentFolder
          ? this.config.parentFolder.id
          : value;
      }
      this.$emit('change', this.internalSelectedFolder);
    },
    findNodeById(nodes, id) {
      for (const node of nodes) {
        if (node.id === id) {
          return node;
        }
        if (node.children && node.children.length > 0) {
          const found = this.findNodeById(node.children, id);
          if (found) return found;
        }
      }
      return null;
    },
    async loadSubFolders(data) {
      const node = data.parentNode;
      const query = {
        folder: node.id === FOLDER_ROOT || node.id === '(root)' ? '' : node.id,
      };

      const response = await fetch(this.config.paths.load_folders + '?' + encodeQueryData(query));
      const subfolders = await response.json();

      // Transform subfolders to include children property for lazy loading
      const folderNodes = subfolders.map(folder => ({
        id: folder.id,
        label: folder.label,
        children: null // Enable lazy loading
      }));

      // Add "Create new folder" action as the last item
      folderNodes.push({
        id: '__create_' + node.id,
        label: this.config.translations.upload_button_create,
        isCreateAction: true,
        parentNode: node,
        children: undefined // No children for this action
      });

      node.children = folderNodes;
      data.callback();
    },
    showCreateFolder(node) {
      this.creatingFolder = true;
      this.creatingFolderParent = node;
      this.newFolderName = '';
      this.createFolderError = null;

      // Select the parent folder
      this.internalSelectedFolder = node.id;
      this.$emit('change', node.id);

      // Focus input in next tick
      this.$nextTick(() => {
        if (this.$refs.newFolderInput) {
          this.$refs.newFolderInput.focus();
        }
      });
    },
    async createFolder() {
      if (!this.newFolderName) {
        return;
      }

      try {
        const formData = new FormData();
        const parentPath = this.creatingFolderParent && this.creatingFolderParent.id !== FOLDER_ROOT && this.creatingFolderParent.id !== '(root)'
          ? this.creatingFolderParent.id
          : '';

        if (parentPath) {
          formData.append('parent', parentPath);
        }
        formData.append('folder', this.newFolderName);

        await axios.post(this.config.paths.create_folder, formData);

        // Create new folder ID and label
        const newFolderId = parentPath ? `${parentPath}/${this.newFolderName}` : this.newFolderName;
        const newFolderLabel = this.newFolderName;

        // Add to parent's children (before the "Create new folder" action)
        if (this.creatingFolderParent && this.creatingFolderParent.children) {
          // Find and remove the "Create new folder" action
          const createActionIndex = this.creatingFolderParent.children.findIndex(child => child.isCreateAction);
          if (createActionIndex !== -1) {
            this.creatingFolderParent.children.splice(createActionIndex, 1);
          }

          // Add the new folder
          this.creatingFolderParent.children.push({
            id: newFolderId,
            label: newFolderLabel,
            children: null
          });

          // Re-add the "Create new folder" action at the end
          this.creatingFolderParent.children.push({
            id: '__create_' + this.creatingFolderParent.id,
            label: this.config.translations.upload_button_create,
            isCreateAction: true,
            parentNode: this.creatingFolderParent,
            children: undefined
          });
        }

        // Manually update folders array to ensure reactivity
        this.folders = [...this.folders];

        // Automatically select the newly created folder
        this.internalSelectedFolder = newFolderId;
        this.$emit('change', newFolderId);

        this.cancelCreateFolder();
      } catch (error) {
        this.createFolderError = (error.response && error.response.data && error.response.data.message) || 'Failed to create folder';
      }
    },
    cancelCreateFolder() {
      this.creatingFolder = false;
      this.creatingFolderParent = null;
      this.newFolderName = '';
      this.createFolderError = null;
    }
  },
  watch: {
    selectedFolder(newValue) {
      this.internalSelectedFolder = newValue;
    }
  }
};
</script>

<style lang="scss">
// Global styles for treeselect menu (when appended to body)
.vue-treeselect__menu {
  z-index: 9999;
}

// Fix alignment for "Create new folder" action items
.vue-treeselect__option[data-id^="__create_"] {
  // Remove the indent padding added by vue-treeselect for nested items
  .vue-treeselect__label-container {
    padding-left: 0;
  }
}
</style>

<style scoped lang="scss">
.folder-tree-select {
  position: relative;

  .folder-option {
    width: 100%;

    .folder-label {
      display: block;
      width: 100%;
    }

    .create-folder-action {
      display: block;
      width: 100%;
      color: #28a745;
      font-style: italic;
      cursor: pointer;
      padding: 2px 0;
      transition: color 0.2s;

      &:hover {
        color: #218838;
      }

      i {
        margin-right: 6px;
        font-size: 12px;
      }
    }
  }

  .create-folder-inline {
    margin-top: 10px;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background-color: #f8f9fa;

    .create-folder-header {
      font-weight: 600;
      margin-bottom: 8px;
      font-size: 13px;
      color: #333;

      i {
        color: #f39c12;
        margin-right: 6px;
      }
    }

    .create-folder-body {
      .new-folder-input {
        width: 100%;
        padding: 6px 8px;
        border: 1px solid #ddd;
        border-radius: 3px;
        font-size: 13px;
        margin-bottom: 8px;

        &:focus {
          outline: none;
          border-color: #4a90e2;
          box-shadow: 0 0 0 2px rgba(74, 144, 226, 0.1);
        }
      }

      .create-folder-actions {
        display: flex;
        gap: 6px;

        .btn {
          padding: 4px 10px;
          font-size: 12px;
          border: none;
          border-radius: 3px;
          cursor: pointer;
          transition: background-color 0.2s;

          i {
            margin-right: 4px;
          }

          &.btn-primary {
            background-color: #28a745;
            color: white;

            &:hover:not(:disabled) {
              background-color: #218838;
            }

            &:disabled {
              background-color: #94d3a2;
              cursor: not-allowed;
            }
          }

          &.btn-default {
            background-color: #6c757d;
            color: white;

            &:hover {
              background-color: #5a6268;
            }
          }
        }
      }
    }

    .create-folder-error {
      margin-top: 8px;
      padding: 6px;
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
      border-radius: 3px;
      font-size: 12px;
    }
  }
}
</style>
