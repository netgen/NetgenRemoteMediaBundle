<template>
  <div>
    <preview
        :field-id="fieldId"
        :selected-image="selectedImage"
        ref="preview"
    ></preview>

    <div :id="'ngremotemedia-buttons-'+fieldId" class="ngremotemedia-buttons" :data-id="fieldId">

      <input type="hidden" :name="this.$root.$data.RemoteMediaInputFields.resource_id" v-model="selectedImage.id" class="media-id" />

      <input v-if="isCroppable" type="button" class="ngremotemedia-scale hid button" @click="handleCropClicked" :value="this.$root.$data.NgRemoteMediaTranslations.interactions_scale" >
      <input v-if="!!selectedImage.id" type="button" @click="handleRemoveMediaClicked" class="ngremotemedia-remove-file button" :value="this.$root.$data.NgRemoteMediaTranslations.interactions_remove_media" />

      <input type="button" @click="handleBrowseMediaClicked" class="ngremotemedia-remote-file button" :value="this.selectedImage.id ? this.$root.$data.NgRemoteMediaTranslations.interactions_manage_media : this.$root.$data.NgRemoteMediaTranslations.interactions_select_media" />

      <div class="ngremotemedia-local-file-container">
        <button type="button" class="btn btn-default ngremotemedia-local-file button upload-from-disk">
          <Label for="new_file">
            {{ this.$root.$data.NgRemoteMediaTranslations.interactions_quick_upload }}
          </Label>
          <input hidden id="new_file" :name="this.$root.$data.RemoteMediaInputFields.new_file" type="file" @change="handleFileInputChange" ref="fileInput">
        </button>
      </div>
    </div>

    <input type="hidden" :name="this.$root.$data.RemoteMediaInputFields.image_variations" v-model="stringifiedVariations" class="media-id"/>
    <crop-modal v-if="cropModalOpen" @change="handleVariationCropChange" @close="handleCropModalClose" :selected-image="selectedImage" :available-variations="config.availableVariations"></crop-modal>
    <media-modal :tags="tags" :selected-media-id="selectedImage.id" v-if="mediaModalOpen" @close="handleMediaModalClose" @media-selected="handleMediaSelected" :paths="config.paths"></media-modal>
    <upload-modal v-if="uploadModalOpen" @close="handleUploadModalClose" @save="handleUploadModalSave" :name="selectedImage.name" ></upload-modal>
  </div>
</template>

<script>

import Preview from "./Preview";
import MediaModal from "./MediaModal";
import CropModal from "./CropModal";
import UploadModal from "./UploadModal";
import {objectFilter} from "../utility/functional";
import {truthy} from "../utility/predicates";

export default {
  name: "Interactions",
  props: ["fieldId", "config", "selectedImage"],
  components: {
    "preview": Preview,
    'media-modal': MediaModal,
    'crop-modal': CropModal,
    'upload-modal': UploadModal,
  },
  computed: {
    isCroppable() {
      return !!this.selectedImage.id && this.selectedImage.mediaType === "image" && Object.keys(this.config.availableVariations).length > 0;
    },
    stringifiedVariations() {
      return JSON.stringify(
          objectFilter(truthy)(this.selectedImage.variations)
      );
    },
  },
  data() {
    return {
      mediaModalOpen: false,
      cropModalOpen: false,
      uploadModalOpen: false,
      folders: [],
      tags: [],
      facetsLoading: true
    };
  },
  methods: {
    prepareDomForModal() {
      const query = document.querySelector('.ez-page-builder-wrapper')
      if (query) {
        query.style.transform = "none";
      }
    },
    resetDomAfterModal() {
      const query = document.querySelector('.ez-page-builder-wrapper')
      if (query) {
        query.removeAttribute("style");
      }
    },
    handleMediaModalClose() {
      this.mediaModalOpen = false;
      this.resetDomAfterModal();
    },
    handleCropModalClose() {
      this.cropModalOpen = false;
      this.resetDomAfterModal();
    },
    handleUploadModalClose() {
      this.uploadModalOpen = false;
    },
    handleEditorInsertModalClose() {
      this.editorInsertModalOpen = false;
    },
    handleMediaSelected(item) {
      this.selectedImage = {
        id: item.resourceId,
        name: item.filename,
        type: item.type,
        mediaType: item.mediaType,
        url: item.url,
        alternateText: item.alt_text,
        tags: item.tags,
        size: item.filesize,
        variations: {},
        height: item.height,
        width: item.width
      };

      this.mediaModalOpen = false;
    },
    handleVariationCropChange(newValues) {
      this.selectedImage = {
        ...this.selectedImage,
        variations: {
          ...this.selectedImage.variations,
          ...newValues
        }
      };
    },
    handleUploadModalSave(name){
      this.selectedImage = {
        ...this.selectedImage,
        name,
        id: name
      };
      this.uploadModalOpen = false;
    },
    handleCropClicked() {
      this.cropModalOpen = true;
      this.prepareDomForModal();
    },
    handleRemoveMediaClicked() {
      this.selectedImage = {
        id: '',
        name: '',
        type: 'image',
        mediaType: 'image',
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
    async fetchFacets() {
      const response = await fetch(this.config.paths.load_facets);
      const data = await response.json();
      this.tags = data.tags;
      this.facetsLoading = false;
    },
    async handleBrowseMediaClicked() {
      this.mediaModalOpen = true;
      this.prepareDomForModal();
      this.fetchFacets();
    },
    getFileType(file){
      const type = file.type.split("/")[0];

      if (type !== "video"){
        return "image";
      }

      return type;
    },
    getFileMediaType(file){
      const type = file.type.split("/")[0];

      if (type !== "video" && type !== "image"){
        return "other";
      }

      return type;
    },
    handleFileInputChange(e) {
      this.uploadModalOpen = true;

      const file = e.target.files.item(0);

      if (file) {
        this.selectedImage = {
          id: file.name,
          name: file.name,
          type: this.getFileType(file),
          mediaType: this.getFileMediaType(file),
          url: '',
          alternateText: '',
          tags: [],
          size: file.size,
          variations: {},
          height: 0,
          width: 0
        };

        if (this.selectedImage.mediaType === "image"){
          const reader = new FileReader();
          reader.addEventListener(
            'load',
            function() {
              this.$refs.preview.$refs.image.onload = function() {
                this.selectedImage.width = this.$refs.preview.$refs.image.naturalWidth,
                    this.selectedImage.height = this.$refs.preview.$refs.image.naturalHeight;
              }.bind(this);

              this.selectedImage.url = reader.result;
            }.bind(this),
            false
          );

          reader.readAsDataURL(file);
        }
      }
    }
  },
  watch: {
    selectedImage: function() {
      this.$emit("selectedImageChanged", this.selectedImage);
    }
  }
};
</script>
