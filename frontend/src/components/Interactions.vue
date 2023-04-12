<template>
  <div>
    <preview
        :field-id="fieldId"
        :config="config"
        :selected-image="selectedImage"
        ref="preview"
    ></preview>

    <div :id="'ngremotemedia-buttons-'+fieldId" class="ngremotemedia-buttons" :data-id="fieldId">

      <input type="hidden" :name="this.config.inputFields.remoteId" v-model="selectedImage.id" class="media-id" />

      <input v-if="isCroppable" type="button" class="ngremotemedia-scale hid button" @click="handleCropClicked" :value="this.config.translations.interactions_scale" >
      <input v-if="!!selectedImage.id" type="button" @click="handleRemoveMediaClicked" class="ngremotemedia-remove-file button" :value="this.config.translations.interactions_remove_media" />

      <input type="button" @click="handleBrowseMediaClicked" class="ngremotemedia-remote-file button" :value="this.selectedImage.id ? this.config.translations.interactions_manage_media : this.config.translations.interactions_select_media" />

      <div v-if="!this.config.disableUpload" class="ngremotemedia-local-file-container">
        <button type="button" class="btn btn-default ngremotemedia-local-file button upload-from-disk" @click="handleScrollTop">
          <Label :for="fieldId + '_file_upload'">
            {{ this.config.translations.interactions_quick_upload }}
          </Label>
          <input hidden :id="fieldId + '_file_upload'" :name="this.config.inputFields.new_file" type="file" @change="handleFileInputChange" ref="fileUploadInput">
        </button>
      </div>
    </div>

    <input type="hidden" :name="this.config.inputFields.cropSettings" v-model="stringifiedVariations" class="media-id"/>
    <crop-modal v-if="cropModalOpen" @change="handleVariationCropChange" @close="handleCropModalClose" :translations="config.translations" :selected-image="selectedImage" :available-variations="this.config.availableVariations"></crop-modal>
    <media-modal :config="config" :tags="tags" :types="types" :visibilities="visibilities" :selected-media-id="selectedImage.id" v-if="mediaModalOpen" @close="handleMediaModalClose" @media-selected="handleMediaSelected" :paths="config.paths"></media-modal>
    <upload-modal v-if="uploadModalOpen" :config="config" :visibilities="visibilities" @close="handleUploadModalClose" @uploaded="handleResourceUploaded" :file="newFile" ></upload-modal>
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
      return !!this.selectedImage.id && this.selectedImage.type === "image" && Object.keys(this.config.availableVariations).length > 0;
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
      types: [],
      folders: [],
      tags: [],
      visibilities: [],
      facetsLoading: true,
      newFile: null,
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
    handleMediaSelected(item) {
      this.selectedImage = {
        id: item.remoteId,
        name: item.filename,
        type: item.type,
        format: item.format,
        url: item.url,
        previewUrl: item.previewUrl,
        alternateText: item.alt_text,
        tags: item.tags,
        size: item.size,
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
    handleResourceUploaded(item){
      this.selectedImage = {
        id: item.remoteId,
        name: item.filename,
        type: item.type,
        format: item.format,
        url: item.url,
        previewUrl: item.previewUrl,
        alternateText: item.alt_text,
        tags: item.tags,
        size: item.size,
        variations: {},
        height: item.height,
        width: item.width
      };

      this.uploadModalOpen = false;
    },
    handleCropClicked() {
      this.cropModalOpen = true;
      this.prepareDomForModal();

      window.scrollTo({
        top: 0,
        behavior: "smooth"
      });
    },
    handleRemoveMediaClicked() {
      this.selectedImage = {
        id: '',
        name: '',
        type: 'image',
        format: '',
        url: '',
        previewUrl: '',
        alternateText: '',
        tags: [],
        size: 0,
        variations: {},
        height: 0,
        width: 0
      };
      this.$refs.fileUploadInput.value = null;
    },
    async fetchFacets() {
      const response = await fetch(this.config.paths.load_facets);
      const data = await response.json();
      this.types = [];
      this.tags = [];
      this.visibilities = [];

      data.types.forEach((type) => {
        if(this.config.allowedTypes.indexOf(type.id) !== -1 || this.config.allowedTypes.length === 0) {
          this.types.push(type);
        }
      });

      data.tags.forEach((tag) => {
        if(this.config.allowedTags.indexOf(tag.id) !== -1 || this.config.allowedTags.length === 0) {
          this.tags.push(tag);
        }
      });

      data.visibilities.forEach((visibility) => {
        if(this.config.allowedVisibilities.indexOf(visibility.id) !== -1 || this.config.allowedVisibilities.length === 0) {
          this.visibilities.push(visibility);
        }
      });

      this.facetsLoading = false;
    },
    async handleBrowseMediaClicked() {
      this.mediaModalOpen = true;
      this.prepareDomForModal();
      this.fetchFacets();

      // Scroll to top to avoid modal being not visible when the Select Media button has been clicked on a long page
      window.scrollTo({
        top: 0,
        behavior: "smooth"
      });
    },
    handleFileInputChange() {
      this.fetchFacets();
      this.uploadModalOpen = true;

      this.newFile = this.$refs.fileUploadInput.files.item(0);
    },
    handleScrollTop() {
      window.scrollTo({
        top: 0,
        behavior: "smooth"
      });
    }
  },
  watch: {
    selectedImage: function() {
      this.$emit("selectedImageChanged", this.selectedImage);
    }
  }
};
</script>
