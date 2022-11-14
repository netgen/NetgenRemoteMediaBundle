<template>
  <modal title="Upload media" @close="$emit('close')">
    <div>
      <select-folder :selected-folder="selectedFolder" @change="handleFolderChange"></select-folder>

      <div class="input-file-name-wrapper">
        <input type="text" v-model="newName"/>
        <button type="button" class="btn btn-blue" :disabled="newName === ''" @click="handleSaveClick">
          {{ this.$root.$data.NgRemoteMediaTranslations.upload_button_save }}
        </button>
      </div>
    </div>
  </modal>
</template>

<script>
import SelectFolder from "./SelectFolder";
import Modal from "./Modal";

export default {
  name: "UploadModal",
  props: ["name"],
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
@import "../scss/variables";

.input-file-name-wrapper {
  padding: 8px 15px;
  background-color: $white;
  box-shadow: inset 1px 0 0 0 $mercury, 0 -1px 0 0 $mercury;
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;

  input {
    width: 40%;
    border: 1px solid $mercury;
    padding: 5px 10px;
    flex-grow: 1;
  }

  button {
    float: right;
  }
}
</style>
