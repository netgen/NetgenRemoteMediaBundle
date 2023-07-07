<template>
    <div>
        <preview
                ref="preview"
                :config="config"
                :field-id="fieldId"
                :selected-image="selectedImage"
                @preview-change="dispatchVanillaChangeEvent"
        ></preview>

        <div :id="'ngremotemedia-buttons-'+fieldId" :data-id="fieldId" class="ngremotemedia-buttons">
            <input v-model="selectedImage.id" :name="this.config.inputFields.remoteId" class="media-id" type="hidden"/>

            <input v-if="isCroppable" :value="this.config.translations.interactions_scale"
                   class="ngremotemedia-scale hid btn" type="button"
                   @click="handleCropClicked">
            <input v-if="!!selectedImage.id" :value="this.config.translations.interactions_remove_media"
                   class="ngremotemedia-remove-file btn"
                   type="button" @click="handleRemoveMediaClicked"/>

            <input :value="this.selectedImage.id ? this.config.translations.interactions_manage_media : this.config.translations.interactions_select_media"
                   class="ngremotemedia-remote-file btn" type="button"
                   @click="handleBrowseMediaClicked"/>

            <div v-if="!this.config.disableUpload" class="ngremotemedia-local-file-container">
                <button class="btn btn-default ngremotemedia-local-file btn upload-from-disk" type="button">
                    <Label :for="fieldId + '_file_upload'">
                        {{ this.config.translations.interactions_quick_upload }}
                    </Label>
                    <input :id="fieldId + '_file_upload'" ref="fileUploadInput" :name="this.config.inputFields.new_file"
                           hidden
                           type="file" @change="handleFileInputChange">
                </button>
            </div>
        </div>

        <input v-model="stringifiedVariations" :name="this.config.inputFields.cropSettings" class="media-id"
               type="hidden"/>
        <portal to="ngrm-body-modal">
            <crop-modal v-if="cropModalOpen" :available-variations="this.config.availableVariations"
                        :selected-image="selectedImage"
                        :translations="config.translations" @change="handleVariationCropChange"
                        @close="handleCropModalClose"></crop-modal>
            <media-modal v-if="mediaModalOpen" :config="config" :paths="config.paths"
                         :selected-media-id="selectedImage.id"
                         :tags="tags" :types="types" :visibilities="visibilities"
                         @close="handleMediaModalClose" @media-selected="handleMediaSelected"></media-modal>
            <upload-modal v-if="uploadModalOpen" :config="config" :file="newFile"
                          :visibilities="visibilities" @close="handleUploadModalClose"
                          @uploaded="handleResourceUploaded"></upload-modal>
        </portal>
        <portal-target class="ngrm-model-portal" name="ngrm-body-modal"></portal-target>
    </div>
</template>

<script>

import Preview from "./Preview";
import MediaModal from "./MediaModal";
import CropModal from "./CropModal";
import UploadModal from "./UploadModal";
import {objectFilter} from "@/utility/functional";
import {truthy} from "@/utility/predicates";

export default {
  name: "Interactions",
  props: ["fieldId", "config", "selectedImage", "order"],
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
    dispatchVanillaChangeEvent() {
      this.$nextTick(function () {
        this.$el.dispatchEvent(
          new CustomEvent(
            'ngrm-change',
            {
              detail: {
                inputFields: this.config.inputFields,
                selectedImage: this.selectedImage,
                order: this.order,
              }
            }
          )
        );
      })
    },
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
      this.dispatchVanillaChangeEvent();
    },
    handleCropModalClose() {
      this.cropModalOpen = false;
      this.resetDomAfterModal();
      this.dispatchVanillaChangeEvent();
    },
    handleUploadModalClose() {
      this.uploadModalOpen = false;
      this.dispatchVanillaChangeEvent();
    },
    handleMediaSelected(item) {
      this.selectedImage = {
        id: item.remoteId,
        name: item.filename,
        type: item.type,
        format: item.format,
        url: item.url,
        previewUrl: item.previewUrl,
        browseUrl: item.browseUrl,
        alternateText: item.altText,
        caption: item.caption,
        watermarkText: this.selectedImage.watermarkText,
        tags: item.tags,
        size: item.size,
        variations: {},
        height: item.height,
        width: item.width
      };

      this.mediaModalOpen = false;
      this.dispatchVanillaChangeEvent();
    },
    handleVariationCropChange(newValues) {
      this.selectedImage = {
        ...this.selectedImage,
        variations: {
          ...this.selectedImage.variations,
          ...newValues
        }
      };

      this.dispatchVanillaChangeEvent();
    },
    handleResourceUploaded(item) {
      this.selectedImage = {
        id: item.remoteId,
        name: item.filename,
        type: item.type,
        format: item.format,
        url: item.url,
        previewUrl: item.previewUrl,
        browseUrl: item.browseUrl,
        alternateText: item.altText,
        caption: item.caption,
        watermarkText: this.selectedImage.watermarkText,
        tags: item.tags,
        size: item.size,
        variations: {},
        height: item.height,
        width: item.width
      };

      this.uploadModalOpen = false;
      this.dispatchVanillaChangeEvent();
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
        format: '',
        url: '',
        previewUrl: '',
        browseUrl: '',
        alternateText: '',
        caption: '',
        watermarkText: this.selectedImage.watermarkText,
        tags: [],
        size: 0,
        variations: {},
        height: 0,
        width: 0
      };
      this.$refs.fileUploadInput.value = null;

      this.dispatchVanillaChangeEvent();
    },
    async fetchFacets() {
      const response = await fetch(this.config.paths.load_facets);
      const data = await response.json();
      this.types = [];
      this.tags = [];
      this.visibilities = [];

      data.types.forEach((type) => {
        if (this.config.allowedTypes.indexOf(type.id) !== -1 || this.config.allowedTypes.length === 0) {
          this.types.push(type);
        }
      });

      data.tags.forEach((tag) => {
        if (this.config.allowedTags.indexOf(tag.id) !== -1 || this.config.allowedTags.length === 0) {
          this.tags.push(tag);
        }
      });

      data.visibilities.forEach((visibility) => {
        if (this.config.allowedVisibilities.indexOf(visibility.id) !== -1 || this.config.allowedVisibilities.length === 0) {
          this.visibilities.push(visibility);
        }
      });

      this.facetsLoading = false;
    },
    async handleBrowseMediaClicked() {
      this.mediaModalOpen = true;
      this.prepareDomForModal();
      this.fetchFacets();
    },
    handleFileInputChange() {
      this.fetchFacets();
      this.uploadModalOpen = true;

      this.newFile = this.$refs.fileUploadInput.files.item(0);
    }
  },
  watch: {
    selectedImage: function () {
      this.$emit("selectedImageChanged", this.selectedImage);
    }
  },
  mounted() {
    this.$nextTick(function () {
      const modalPortal = document.querySelector('.ngrm-model-portal');

      document.body.prepend(modalPortal);
    })
  },
};
</script>
