<template>
  <div class="form-field">
    <v-select
      :options="foldersWithNew"
      label="name"
      v-model="folder"
      @input="handleFolderChanged"
      :reduce="option => option.id"

      placeholder="/"
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
          <button type="button">Create new</button>
        </div>
        <div v-else-if="option.added">{{option.name}} (new)</div>
        <div v-else>{{option.name}}</div>
      </template>
    </v-select>
  </div>
</template>

<script>
import vSelect from "vue-select";
import Modal from "./Modal";

export default {
  name: "SelectFolder",
  props: ["folders", "selectedFolder"],
  data() {
    return {
      folderSearchQuery: "",
      addedFolders: [],
      folder: this.selectedFolder
    };
  },
  computed: {
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
    handleFolderChanged(value) {
      this.folderSearchQuery = "";

      var allFolders = [...this.folders, ...this.addedFolders];

      if (!allFolders.find(folder => folder.name === value)) {
        this.addedFolders.push({ id: value, name: value, added: true });
      }

      this.$emit("change", this.folder );
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
  .form-field label {
    font-size: 12px;
    font-weight: 700;
    line-height: 18px;
    color: $boulder;
    margin-bottom: 3px;
    display: block;
  }

</style>
