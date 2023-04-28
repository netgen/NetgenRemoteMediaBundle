<template>
  <div>
    <input type="hidden" :name="config.inputFields.folder" v-model="selectedFolder">

    <span v-if="selectedFolder"><i class="fa fa-folder"></i> {{ this.selectedFolder }}</span>
    <span v-else><i>{{ this.config.translations.select_folder_interaction_empty }}</i></span>

    <div class="ngremotemedia-buttons">
      <input v-if="selectedFolder" type="button" @click="handleFolderRemove" value="Remove folder" />
      <input type="button" @click="handleSelectFolderModalOpen" :value="this.config.translations.select_folder_interaction_button" />
    </div>

    <modal v-if="selectFolderModalOpen" title="Select folder" @close="handleSelectFolderModalClose">
      <select-folder :config="config" :selected-folder="selectedFolder" @select="handleFolderSelect"></select-folder>
    </modal>
  </div>
</template>

<script>

import SelectFolder from "./SelectFolder.vue";
import Modal from "./Modal.vue";

export default {
  name: "SelectFolderInteraction",
  props: ["config", "selectedFolder"],
  components: {
    "select-folder": SelectFolder,
    "modal": Modal,
  },
  data() {
      return {
        selectFolderModalOpen: false,
      };
  },
  methods: {
      handleSelectFolderModalOpen() {
          console.log(this.selectedFolder);
          this.selectFolderModalOpen = true;
      },
      handleSelectFolderModalClose() {
          this.selectFolderModalOpen = false;
      },
      handleFolderSelect(folder) {
          this.selectedFolder = folder;
          this.handleSelectFolderModalClose();
      },
      handleFolderRemove() {
          this.selectedFolder = null;
      }
  }
};
</script>
