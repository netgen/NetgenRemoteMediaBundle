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
          :options="folders"
          label="name"
          v-model="selectedFolder"
          @input="handleFolderChange"
          :reduce="option => option.id"
          placeholder="All"
        />
      </div>

      <div class="search-wrapper">
        <span class="search-label">Search</span>
        <div class="search">
            <ul class="searchType">
                <li class="active">
                    <span>Name</span>
                </li>
            </ul>
            <input
            type="text"
            :placeholder="`Search by name`"
            v-model="query"
            @keyup="handleQueryChange"
            />
        </div>
          <div class="search">
              <ul class="searchType">
                  <li>
                      <span>Tag</span>
                  </li>
              </ul>
              <input
                  type="text"
                  :placeholder="`With tag`"
                  v-model="tag"
                  @keyup="handleTagChange"
              />
          </div>
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
      query: this.facets.query,
    };
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
    handleFolderChange(value) {
      this.$emit("change", { folder: this.selectedFolder });
    },
    handleQueryChange() {
      this.$emit("change", { query: this.query });
    },
    handleTagChange() {
      this.$emit("change", { tag: this.tag });
    }
  },
  components: {
    "v-select": vSelect
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

  .tabs {
    list-style: none;
    display: flex;
    align-items: center;
    padding: 0 15px;
    margin: 0;

    li {
      font-size: 14px;
      font-weight: 700;
      line-height: 16px;
      text-align: center;
      text-transform: uppercase;
      flex-grow: 1;
      color: $dusty-gray;
      cursor: pointer;
      min-width: 120px;

      span {
        display: inline-block;
        padding: 17px 20px;
      }

      &.active {
        color: $netgen-primary;
        box-shadow: inset 0 -4px 0 0 $netgen-primary;
      }
    }
  }

  .body {
    box-shadow: inset 0 -1px 0 0 $mercury, inset 0 1px 0 0 $mercury, inset -1px 0 0 0 $mercury;
    background: $white;
    padding: 30px 15px;

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

          li {
            cursor: pointer;
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
          margin-left: 14px;
          flex-grow: 1;
        }
      }
    }
  }
}
</style>
