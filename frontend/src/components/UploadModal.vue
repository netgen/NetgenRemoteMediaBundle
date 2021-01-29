<template>
  <modal title="Upload image" @close="$emit('close')">
    <div v-if="!loading">
      <label for="folder">Select Folder</label>
      <select-folder :folders="folders" @change="handleFolderChange"></select-folder>
      <input
        type="text"
        v-model="newName"
      />
      <button :disabled="newName === ''" type="button" @click="handleSaveClick">Save</button>
    </div>
    <i v-else class="ng-icon ng-spinner" />
  </modal>
</template>

<script>
import SelectFolder from "./SelectFolder";
import Modal from "./Modal";

export default {
  name: "UploadModal",
  props: ["folders", "loading", "name"],
  data() {
    return {
      selectedFolder: "",
      newName: this.name
    };
  },
  components: {
    "select-folder": SelectFolder,
    'modal': Modal
  },
  methods: {
    handleFolderChange(folder) {
      this.selectedFolder = folder;
    },
    handleSaveClick() {
      let newName = this.newName;
      if (this.selectedFolder){
        newName = `${this.selectedFolder}/${this.newName}`;
      }
      this.$emit("save", newName);
    }
  }
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped lang="scss">
</style>
