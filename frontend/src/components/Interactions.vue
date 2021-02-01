<template>
  <div>
    <preview
      :field-id="fieldId"
      :base="base"
      :selected-image="selectedImage"
      :alt-text="alt_text"
      ref="preview"
    ></preview>

    <div :id="'ngremotemedia-buttons-'+fieldId" class="ngremotemedia-buttons"
         :data-id="fieldId"
         :data-contentobject-id="contentObjectId"
         :data-version="version">

      <input type="hidden" :name="base+'_media_id_'+fieldId" v-model="selectedImage.id" class="media-id" />

      <input v-if="isCroppable" type="button" class="ngremotemedia-scale hid button" @click="handleCropClicked" :value="this.$root.$data.NgRemoteMediaTranslations.interactions_scale" >
      <input v-if="!!selectedImage.id" type="button" @click="handleRemoveMediaClicked" class="ngremotemedia-remove-file button" :value="this.$root.$data.NgRemoteMediaTranslations.interactions_remove_media" />

      <input type="button" @click="handleBrowseMediaClicked" class="ngremotemedia-remote-file button" :value="this.$root.$data.NgRemoteMediaTranslations.interactions_manage_media" />

      <div class="ngremotemedia-local-file-container">
        <button type="button" class="btn btn-default ngremotemedia-local-file button upload-from-disk">
          <Label for="new_file">
            {{ this.$root.$data.NgRemoteMediaTranslations.interactions_quick_upload }}
          </Label>
          <input hidden id="new_file" :name="base+'_new_file_'+fieldId" type="file" @change="handleFileInputChange" ref="fileInput">
        </button>
      </div>
    </div>
  </div>
</template>

<script>

import Preview from "./Preview";
import vSelect from "vue-select";

export default {
  name: "Interactions",
  props: ["contentObjectId", "version", "fieldId", "base", "selectedImage"],
  components: {
    "preview": Preview
  },
  computed: {
    isCroppable() {
      return !!this.selectedImage.id && this.selectedImage.type === "image" && Object.keys(this.$root.$data.config.availableVariations).length > 0;
    }
  },
  methods: {
    handleCropClicked() {
      this.$root.$data.cropModalOpen = true;
      this.$root.$options.methods.prepareDomForModal();
    },
    handleRemoveMediaClicked() {
      this.selectedImage = {
        id: '',
        name: '',
        type: 'image',
        url: '',
        alternateText: '',
        tags: [],
        size: 0,
        variations: {},
        height: 0,
        width: 0
      };
      this.$refs.fileInput.value = null;
    },
    async fetchFolders() {
      const response = await fetch(this.$root.$data.config.paths.folders);
      const folders = await response.json();
      this.$root.$data.folders = folders;
    },
    async handleBrowseMediaClicked() {
      this.$root.$data.mediaModalOpen = true;
      this.$root.$options.methods.prepareDomForModal();
      this.fetchFolders();
    },
    getFileType(file){
      const type = file.type.split("/")[0];

      if (type !== "video" && type !== "image"){
        return "other";
      }

      return type;
    },
    handleFileInputChange(e) {
      this.$root.$data.uploadModalOpen = true;
      this.$root.$data.uploadModalLoading = true;

      this.fetchFolders();

      const file = e.target.files.item(0);

      if (file) {
        this.selectedImage = {
          id: file.name,
          name: file.name,
          type: this.getFileType(file),
          url: '',
          alternateText: '',
          tags: [],
          size: file.size,
          variations: {},
          height: 0,
          width: 0
        };

        if (this.selectedImage.type === "image"){
          const reader = new FileReader();
          reader.addEventListener(
              'load',
              function() {
                this.$refs.preview.$refs.image.onload = function() {
                  this.selectedImage.width = this.$refs.preview.$refs.image.naturalWidth,
                      this.selectedImage.height = this.$refs.preview.$refs.image.naturalHeight;
                  this.$root.$data.uploadModalLoading = false;
                }.bind(this);

                this.selectedImage.url = reader.result;
              }.bind(this),
              false
          );

          reader.readAsDataURL(file);
        } else {
          this.$root.$data.uploadModalLoading = false;
        }
      }
    },
  }
};
</script>
