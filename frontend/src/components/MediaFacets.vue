<template>
  <div class="mediaFacets">
    <ul class="tabs">
      <li :class="{active: isType(TYPE_IMAGE)}">
        <span @click="handleTypeChange(TYPE_IMAGE)">Image and documents</span>
      </li>
      <li :class="{active: isType(TYPE_VIDEO)}">
        <span @click="handleTypeChange(TYPE_VIDEO)">Video</span>
      </li>
    </ul>
    <div class="body">
      <div class="form-field">
        <label for="folder">Select Folder</label>
        <v-select
          :options="foldersWithNew"
          label="name"
          v-model="selectedFolder"
          @input="handleFolderChange"
          :reduce="option => option.id"
          placeholder="showing all files"
        >
          <template v-slot:search="search">
            <input
              class="vs__search"
              v-bind="search.attributes"
              v-on="search.events"
              v-model="folderSearchQuery"
            />
          </template>

          <template v-slot:option="option">
            <div v-if="option.new">
              {{option.name}}
              <button>Create new</button>
            </div>
            <div v-else-if="option.added">{{option.name}} (new)</div>
            <div v-else>{{option.name}}</div>
          </template>
        </v-select>
      </div>

      <div class="search">
        <ul class="searchType">
          <li :class="{active: isSearch(SEARCH_NAME)}">
            <span @click="handleSearchChange(SEARCH_NAME)">Name</span>
          </li>
          <li :class="{active: isSearch(SEARCH_TAG)}">
            <span @click="handleSearchChange(SEARCH_TAG)">Tag</span>
          </li>
        </ul>
        <input
          type="text"
          :placeholder="`Search by ${searchName}`"
          v-model="query"
          @keyup="handleQueryChange"
        />
      </div>
    </div>
  </div>
</template>

<script>
import {
  TYPE_IMAGE,
  TYPE_VIDEO,
  SEARCH_NAME,
  SEARCH_TAG,
  FOLDER_ALL
} from "../constants/facets";

import vSelect from "vue-select";

export default {
  name: "MediaFacets",
  props: ["folders", "facets"],
  data() {
    return {
      TYPE_IMAGE,
      TYPE_VIDEO,
      SEARCH_NAME,
      SEARCH_TAG,
      FOLDER_ALL,
      selectedFolder: this.facets.folder,
      folderSearchQuery: "",
      query: this.facets.query,
      addedFolders: []
    };
  },
  computed: {
    searchName() {
      return this.facets.searchType === SEARCH_NAME ? "name" : "tag";
    },
    foldersWithNew() {
      const allFolders = [...this.folders, ...this.addedFolders];

      if (this.folderSearchQuery.length === 0) {
        return allFolders;
      }

      if (allFolders.find(folder => folder.name === this.folderSearchQuery)) {
        return allFolders;
      }

      return [
        {
          name: this.folderSearchQuery,
          id: this.folderSearchQuery,
          new: true
        },
        ...allFolders
      ];
    }
  },
  methods: {
    handleSearchChange(searchType) {
      this.$emit("change", { searchType });
    },
    handleTypeChange(mediaType) {
      this.$emit("change", { mediaType });
    },
    isType(type) {
      return this.facets.mediaType === type;
    },
    isSearch(type) {
      return this.facets.searchType === type;
    },
    handleFolderChange(value) {
      this.folderSearchQuery = "";
      this.addedFolders.push({ id: value, name: value, added: true });
      this.$emit("change", { folder: this.selectedFolder });
    },
    handleQueryChange() {
      this.$emit("change", { query: this.query });
    }
  },
  components: {
    "v-select": vSelect
  }
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped lang="scss">
.mediaFacets {
  ul {
    list-style: none;

    li {
      display: inline;
      padding: 1em;
      &.active {
        text-decoration: underline;
      }
    }
  }
}
</style>
