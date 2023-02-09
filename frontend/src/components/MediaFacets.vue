<template>
  <div class="mediaFacets">
    <div class="body">
      <div v-if="types.length > 1" class="form-field">
        <label for="type">{{ this.$root.$data.NgRemoteMediaTranslations.browse_select_type }}</label>
        <v-select
            :options="types"
            label="name"
            v-model="selectedType"
            @input="handleTypeChange"
            :reduce="option => option.id"
            :placeholder="facetsLoading ? this.$root.$data.NgRemoteMediaTranslations.browse_loading_types : this.$root.$data.NgRemoteMediaTranslations.browse_all_types"
        />
      </div>

      <div v-if="folders" class="form-field">
        <label for="folder">{{ this.$root.$data.NgRemoteMediaTranslations.browse_select_folder }}</label>
        <treeselect
          :multiple="false"
          :options="folders"
          :load-options="loadSubFolders"
          v-model="selectedFolder"
          :value="this.$root.$data.NgRemoteMediaOptions.parentFolder ? this.$root.$data.NgRemoteMediaOptions.parentFolder.id : ''"
          @input="handleFolderChange"
          :placeholder="facetsLoading ? this.$root.$data.NgRemoteMediaTranslations.browse_loading_folders : this.$root.$data.NgRemoteMediaTranslations.browse_all_folders"
          :disabled="facetsLoading"
          :beforeClearAll="clearFolderField"
        />
      </div>

      <div v-if="tags.length > 1" class="form-field">
        <label for="tag">{{ this.$root.$data.NgRemoteMediaTranslations.browse_select_tag }}</label>
        <v-select
            :options="tags"
            label="name"
            v-model="tag"
            @input="handleTagChange"
            :reduce="option => option.id"
            :placeholder="facetsLoading ? this.$root.$data.NgRemoteMediaTranslations.browse_loading_tags : this.$root.$data.NgRemoteMediaTranslations.browse_all_tags"
            :disabled="facetsLoading"
        />
      </div>

      <div v-if="visibilities.length > 1" class="form-field">
        <label for="visibilities">{{ this.$root.$data.NgRemoteMediaTranslations.browse_select_visibility }}</label>
        <v-select
          :options="visibilities"
          label="name"
          v-model="visibility"
          @input="handleVisibilityChange"
          :reduce="option => option.id"
          :placeholder="facetsLoading ? this.$root.$data.NgRemoteMediaTranslations.browse_loading_visibilities : this.$root.$data.NgRemoteMediaTranslations.browse_all_visibilities"
          :disabled="facetsLoading"
        />
      </div>

      <div class="search-wrapper">
        <span class="search-label">{{ this.$root.$data.NgRemoteMediaTranslations.search }}</span>
        <div class="search">
          <input
            type="text"
            :placeholder="this.$root.$data.NgRemoteMediaTranslations.search_placeholder"
            v-model="query"
            @keyup="handleQueryChange"
            @keydown.enter.prevent="null"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {
  TYPE_ALL,
  TYPE_IMAGE,
  TYPE_VIDEO,
  TYPE_RAW,
  FOLDER_ALL,
  FOLDER_ROOT,
  TAG_ALL,
} from "../constants/facets";

import vSelect from "vue-select";
import Treeselect from '@riophae/vue-treeselect';
import '@riophae/vue-treeselect/dist/vue-treeselect.css'
import {encodeQueryData} from "@/utility/utility";

export default {
  name: "MediaFacets",
  props: ["tags", "types", "visibilities", "facets", "facetsLoading"],
  data() {
    return {
      TYPE_ALL,
      TYPE_IMAGE,
      TYPE_VIDEO,
      TYPE_RAW,
      FOLDER_ALL,
      FOLDER_ROOT,
      TAG_ALL,
      folders: [{
        id: this.$root.$data.NgRemoteMediaOptions.parentFolder ? this.$root.$data.NgRemoteMediaOptions.parentFolder.id : FOLDER_ROOT,
        label: this.$root.$data.NgRemoteMediaOptions.parentFolder ? this.$root.$data.NgRemoteMediaOptions.parentFolder.label : FOLDER_ROOT,
        children: null
      }],
      selectedFolder: this.facets.folder,
      selectedType: this.facets.type,
      query: this.facets.query,
      tag: this.facets.tag,
      visibility: this.facets.visibility
    };
  },
  methods: {
    clearFolderField() {
      if (this.$root.$data.NgRemoteMediaOptions.parentFolder) {
        this.selectedFolder = this.$root.$data.NgRemoteMediaOptions.parentFolder.id;

        return false;
      }

      return true;
    },
    handleTypeChange(type) {
      this.$emit("change", { type });
    },
    handleFolderChange(value) {
      this.selectedFolder = value;
      if (typeof value === 'undefined' || !value) {
        this.selectedFolder = this.$root.$data.NgRemoteMediaOptions.parentFolder
          ? this.$root.$data.NgRemoteMediaOptions.parentFolder.id
          : value;
      }
      this.$emit("change", { folder: this.selectedFolder });
    },
    handleQueryChange() {
      this.$emit("change", { query: this.query });
    },
    handleTagChange() {
      this.$emit("change", { tag: this.tag });
    },
    handleVisibilityChange() {
      this.$emit("change", { visibility: this.visibility });
    },
    async loadSubFolders(data) {
      const node = data.parentNode;
      const query = {
        folder: node.id === '(root)' ? '' : node.id,
      };

      const response = await fetch(this.$root.$data.config.paths.load_folders+'?'+encodeQueryData(query));
      node.children = await response.json();
      data.callback();
    }
  },
  components: {
    "v-select": vSelect,
    "treeselect": Treeselect
  }
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped lang="scss">
@import "../scss/variables";

.mediaFacets {
  width: 362px;
  flex-shrink: 0;
  box-shadow: inset -1px 0 0 0 $mercury;

  .body {
    box-shadow: inset 0 -1px 0 0 $mercury, inset 0 1px 0 0 $mercury, inset -1px 0 0 0 $mercury;
    background: $white;
    padding: 30px 15px;

    .form-field + .form-field {
      margin-top: 15px;
    }

    .form-field label,
    .search-wrapper .search-label {
      font-size: 12px;
      font-weight: 700;
      line-height: 18px;
      color: $boulder;
      margin-bottom: 3px;
      display: block;
    }

    .search-wrapper {
      margin: 30px 0 0;;

      .search {
        display: flex;
        align-items: center;
        margin: 5px 0;

        ul, input {
          font-size: 14px;
          line-height: 16px;
        }

        ul {
          margin: 0;
          padding: 5px;
          list-style: none;
          display: flex;
          align-items: center;
          border: 1px solid $mercury;
          min-width: 75px;
          display: none;

          li {
            cursor: auto;
            margin-right: 10px;
            padding: 4px 10px;
            min-width: 45px;

            &:last-child,
            &:only-child {
              margin: 0;
            }

            &.active {
              background: $netgen-primary;
              color: $white;
              border-radius: 4px;
              box-shadow: inset -1px 0 0 0 $alto, inset 1px 0 0 0 $alto, inset 0 1px 0 0 $alto, inset 0 -1px 0 0 $alto;
            }
          }
        }

        input {
          border: 1px solid $mercury;
          padding: 9px 10px;
          // margin-left: 14px;
          flex-grow: 1;
        }
      }
    }
  }
  .ng-spinner {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);

    &:before {
      display: inline-block;
      animation: spinning 1500ms linear infinite;
    }
  }
}
</style>
