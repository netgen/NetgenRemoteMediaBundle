<template>
  <div class="folderGalery">
    <div :class="loading ? 'items loading' : 'items'">
      <div class="breadcrumbs">
        <span>{{ this.$root.$data.NgRemoteMediaTranslations.upload_breadcrumbs_info }} </span>
        <span v-for="(folder, index) in breadcrumbs" :key="index">
        <span v-if="index !== 0"> / </span>
        <a v-if="index !== breadcrumbs.length - 1" href="#" @click="openFolder(folder.id)">
          {{folder.label}}
        </a>
        <span v-else>{{folder.label}}</span>
        </span>
      </div>

      <div class="info">
        <i class="fa fa-info-circle"></i>
        {{ this.$root.$data.NgRemoteMediaTranslations.upload_info_text }}
      </div>

      <div class="media" v-for="folder in folders" :key="folder.id" :class="{selected: folder.id === selectedFolder}">
        <div class="media-container" v-on:dblclick="openFolder(folder.id)">
          <span class="file-placeholder">
            <span class="icon-doc">
              <i class="fa fa-folder"></i>
            </span>
          </span>
          <Label class="filename">{{folder.label}}</Label>
        </div>
        <button type="button" @click="$emit('change', folder.id)" class="btn btn-blue select-btn">
          {{ _self.$root.$data.NgRemoteMediaTranslations.upload_button_select }}
        </button>
      </div>
      <div class="media new-folder">
        <div class="media-container">
          <span class="file-placeholder">
            <span class="icon-doc">
              <i class="fa fa-folder"></i>
            </span>
          </span>
          <input type="text" v-model="newFolder" :placeholder="this.$root.$data.NgRemoteMediaTranslations.upload_placeholder_new_folder"/>
        </div>
        <button type="button" class="btn btn-blue select-btn" :disabled="newFolder === null" @click="createNewFolder">
          {{ this.$root.$data.NgRemoteMediaTranslations.upload_button_create }}
        </button>
      </div>
    </div>
    <i v-if="loading" class="ng-icon ng-spinner" />
  </div>
</template>

<script>

import {encodeQueryData} from "@/utility/utility";
import axios from 'axios';
import {FOLDER_ROOT} from "../constants/facets";


export default {
  name: "SelectFolder",
  props: ["selectedFolder"],
  data() {
    return {
      folders: [],
      newFolder: null,
      breadcrumbs: [],
      loading: false
    };
  },
  methods: {
    openFolder(folderPath) {
      this.$emit('change', folderPath);
      this.loadSubFolders(folderPath);
    },

    async loadSubFolders(folderPath) {
      this.loading = true;
      var ajaxUrl = this.$root.$data.config.paths.load_folders;
      if (folderPath !== null) {
        const query = {
          folder: folderPath === FOLDER_ROOT ? '' : folderPath,
        };

        ajaxUrl += '?' + encodeQueryData(query);
      }

      const response = await fetch(ajaxUrl);
      this.folders = await response.json();

      this.generateBreadcrumbs(folderPath);
      this.newFolder = null;
      this.loading = false;
    },
    generateBreadcrumbs(folderPath) {
      this.breadcrumbs = [];

      let rootFolder = {
        'id': null,
        'label': this.$root.$data.NgRemoteMediaTranslations.upload_root_folder
      };

      if (folderPath === null) {
        this.breadcrumbs.push(rootFolder);

        return;
      }

      const folders = folderPath.split('/');
      var pathArray = [];

      let parentFolders = [];
      if (this.$root.$data.NgRemoteMediaConfig.parentFolder) {
        parentFolders = this.$root.$data.NgRemoteMediaConfig.parentFolder.id.split('/');
        rootFolder = {
          'id': this.$root.$data.NgRemoteMediaConfig.parentFolder.id,
          'label': this.$root.$data.NgRemoteMediaConfig.parentFolder.label
        };
      }

      this.breadcrumbs.push(rootFolder);

      folders.forEach((value, index) => {
        pathArray.push(value);
        if (parentFolders.indexOf(value) < 0) {
          this.breadcrumbs.push({
            'id': pathArray.join('/'),
            'label': value
          });
        }
      });
    },
    async createNewFolder() {
      this.loading = true;

      var data = new FormData();

      if (this.selectedFolder) {
        data.append('parent', this.selectedFolder);
      }

      data.append('folder', this.newFolder);

      await axios.post(this.$root.$data.config.paths.create_folder, data);
      this.folders.push({
        'id': this.selectedFolder !== null ? this.selectedFolder + '/' + this.newFolder : this.newFolder,
        'label': this.newFolder
      });
      this.newFolder = null;
      this.loading = false;
    }
  },
  created() {
    if (this.$root.$data.NgRemoteMediaConfig.folder) {
      folder = this.$root.$data.NgRemoteMediaConfig.folder.id;
      this.allowCreate = false;

      this.$emit('change', folder);
      this.generateBreadcrumbs(folder);

      return;
    }
    let folder = null;
    if (this.$root.$data.NgRemoteMediaConfig.parentFolder) {
      folder = this.$root.$data.NgRemoteMediaConfig.parentFolder.id;
    }
    this.openFolder(folder);

  }
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped lang="scss">
@import "../scss/variables";

.folderGalery {
  position: relative;
  flex-grow: 1;
  height: calc(100% - 50px);
  overflow-y: auto;

  .items {
    padding: 15px;

    &.loading {
      opacity: 0.5;
    }

    .breadcrumbs {
      background-color: $white;
      width: 100%;
      margin-bottom: 20px;
      padding: 10px;

      a {
        color: $netgen-primary;
      }
    }

    .info {
      font-style: italic;
      margin-bottom: 10px;
      margin-left: 10px;
    }

    .media {
      width: 177px;
      min-height: 182px;
      max-height: 190px;
      padding: 8px;
      margin: 0 15px 15px 0;
      background-color: $white;

      display: inline-block;

      .media-container {
        width: 100%;
      }

      .img {
        display: block;
        margin-bottom: 4px;
        object-fit: cover;
        height: 92px;
        width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
      }

      .file-placeholder {
        position: relative;
        height: 92px;
        display: block;
        margin-bottom: 4px;

        .icon-doc {
          position: absolute;
          top: 50%;
          left: 50%;
          transform: translate(-50%, -50%);
          color: $white;
          font-size: 40px;
        }

        &:before {
          position: absolute;
          content: '';
        }

        &:before {
          background-color: rgba(0, 0, 0, .7);
          top: 0;
          bottom: 0;
          left: 0;
          right: 0;
        }
      }

      &.new-folder {
        input {
          width: 100%;
          margin-top: 5px;
        }

        .select-btn {
          background: seagreen;
        }

        .file-placeholder {
          &:before {
            background-color: rgba(0, 0, 0, .2);
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
          }
        }
      }

      .filename {
        overflow: hidden;
        display: inline-block;
        text-overflow: ellipsis;
        white-space: nowrap;
        width: 100%;
        text-align: center;
        font-size: 16px;
        line-height: 20px;
        margin-top: 4px;
        margin-bottom: 0;
      }

      .size-description {
        font-size: 12px;
        line-height: 14px;
        text-align: center;
        color: $dusty-gray;

        .format {
          text-transform: uppercase;
        }
      }

      &.selected {
        border: 1px solid $netgen-primary;
      }

      .select-btn {
        margin-top: 10px;
        padding: 3px;
        width: 100%;
      }
    }
  }

  .folder-empty {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);

    span {
      display: block;
      text-align: center;
      font-size: 14px;
      line-height: 16px;

      &.ngrm-icon-folder {
        color: $dusty-gray;
        font-size: 33px;
      }

      strong {
        display: block;
        margin: 5px 0;
        font-size: 16px;
        line-height: 19px;
      }
    }
  }

  .load-more-wrapper {
    padding: 8px 15px;
    background-color: $white;
    text-align: right;
    box-shadow: inset 1px 0 0 0 $mercury, 0 -1px 0 0 $mercury;
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
  }
}

.ng-spinner {
  position: fixed;
  vertical-align: center;
  left: 50%;
  transform: translate(-50%, -50%);

  &:before {
    display: inline-block;
    animation: spinning 1500ms linear infinite;
  }
}
</style>
