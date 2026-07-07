<template>
  <div>
    <preview
      ref="preview"
      :config="config"
      :field-id="fieldId"
      :selected-image="localSelectedImage"
      :is-croppable="hasCroppableVariations"
      @preview-change="dispatchVanillaChangeEvent"
      @remove-resource="handleRemoveResource"
      @reorder-resources="handleReorderResources"
      @update-resource="handleUpdateResource"
      @move-resource="handleMoveResource"
      @crop-resource="handleCropClicked"
    >
    </preview>

    <div
      v-if="limitNotice"
      class="limit-indicator limit-reached"
      role="alert"
    >
      <i class="fa fa-info-circle"></i>
      <span>{{ limitNotice }}</span>
    </div>

    <div
      :id="'ngremotemedia-buttons-' + fieldId"
      :data-id="fieldId"
      class="ngremotemedia-buttons"
    >
      <input
        :value="getBrowseButtonLabel()"
        class="ngremotemedia-remote-file btn"
        type="button"
        @click="handleBrowseMediaClicked"
      />
    </div>

    <multi-upload-inline
      v-if="!config.disableUpload"
      :config="config"
      :visibilities="visibilities"
      :upload-limit="uploadLimit"
      :current-count="currentFileCount"
      @all-uploaded="handleMultiResourcesUploaded"
    >
    </multi-upload-inline>

    <portal :to="`ngrm-body-modal-${fieldId}`">
      <crop-modal
        v-if="cropModalOpen"
        :available-variations="config.availableVariations"
        :selected-image="cropTargetImage"
        :translations="config.translations"
        @change="handleVariationCropChange"
        @close="handleCropModalClose"
      >
      </crop-modal>

      <media-modal
        v-if="mediaModalOpen"
        :config="config"
        :paths="config.paths"
        :selected-media-id="localSelectedImage.id"
        :tags="tags"
        :types="types"
        :visibilities="visibilities"
        :facets-loading="facetsLoading"
        :multi-select="isCollectionMode"
        :selection-limit="uploadLimit"
        :current-count="currentFileCount"
        @close="handleMediaModalClose"
        @media-selected="handleMediaSelected"
        @media-multi-selected="handleMediaMultiSelected"
      >
      </media-modal>
    </portal>
    <portal-target
      :class="`ngrm-model-portal-${fieldId}`"
      :name="`ngrm-body-modal-${fieldId}`"
    >
    </portal-target>
  </div>
</template>

<script>
import Preview from "./Preview";
import MediaModal from "./MediaModal";
import CropModal from "./CropModal";
import MultiUploadInline from "./MultiUploadInline";

let resourceUidCounter = 0;
const nextResourceUid = () => `ngrm-r-${Date.now().toString(36)}-${++resourceUidCounter}`;
const createEmptyImageState = (source = null) => ({
  id: "",
  locationId: "",
  name: "",
  type: "image",
  format: "",
  url: "",
  previewUrl: "",
  browseUrl: "",
  alternateText: "",
  caption: "",
  watermarkText: "",
  tags: [],
  size: 0,
  variations: {},
  height: 0,
  width: 0,
  selectedVariation: null,
  cssClass: "",
  source,
  uploadedResources: [],
});

export default {
  name: "Interactions",
  props: ["fieldId", "config", "selectedImage"],
  components: {
    preview: Preview,
    "media-modal": MediaModal,
    "crop-modal": CropModal,
    "multi-upload-inline": MultiUploadInline,
  },
  computed: {
    uploadLimit() {
      return Number(this.config.uploadLimit || 0);
    },
    isCollectionMode() {
      if (this.config.isCollection !== undefined && this.config.isCollection !== null) {
        return !!this.config.isCollection;
      }

      return this.uploadLimit !== 1;
    },
    resources() {
      return this.localSelectedImage.uploadedResources || [];
    },
    currentFileCount() {
      return this.resources.length;
    },
    hasCroppableVariations() {
      return Object.keys(this.config.availableVariations).length > 0;
    },
    cropTargetImage() {
      return this.resources[this.cropTargetIndex] || this.localSelectedImage;
    },
  },
  data() {
    return {
      localSelectedImage: this.normalizeSelectedImage(this.selectedImage),
      mediaModalOpen: false,
      cropModalOpen: false,
      types: [],
      tags: [],
      visibilities: [],
      facetsLoading: true,
      cropTargetIndex: 0,
      limitNotice: null,
      limitNoticeTimer: null,
    };
  },
  methods: {
    getBrowseButtonLabel() {
      return this.config.translations.interactions_manage_media;
    },

    handleRemoveResource(index) {
      const resources = [...this.resources];
      resources.splice(index, 1);
      this.setResources(resources);
      this.dispatchVanillaChangeEvent();
    },

    handleReorderResources(newList) {
      const ordered = Array.isArray(newList) ? newList : this.resources;
      this.setResources(ordered);
      this.dispatchVanillaChangeEvent();
    },

    handleUpdateResource({ index, field, value }) {
      if (index < 0 || index >= this.resources.length) return;
      const resources = this.resources.map((resource, i) =>
        i === index ? { ...resource, [field]: value } : resource
      );
      this.setResources(resources);
      this.dispatchVanillaChangeEvent();
    },

    handleMoveResource({ from, to }) {
      const resources = [...this.resources];
      if (from < 0 || from >= resources.length || to < 0 || to >= resources.length) {
        return;
      }

      const [movedItem] = resources.splice(from, 1);
      resources.splice(to, 0, movedItem);
      this.setResources(resources);
      this.dispatchVanillaChangeEvent();
    },

    handleMediaMultiSelected(items) {
      const newResources = items.map((item) => this.normalizeResource(item));
      this.setResources([...this.resources, ...newResources]);
      this.mediaModalOpen = false;
      this.resetDomAfterModal();
      this.dispatchVanillaChangeEvent();
    },

    normalizeSelectedImageForCollectionMode() {
      if (this.resources.length > 0) {
        this.setResources(this.resources.map((item) => this.normalizeResource(item)));
        return;
      }

      this.localSelectedImage = this.normalizeSelectedImage(this.localSelectedImage);
    },

    getEmptyImageState() {
      return createEmptyImageState(this.config.locationSource || null);
    },

    normalizeResource(item) {
      return {
        uid: item.uid || nextResourceUid(),
        id: item.remoteId || item.remote_id || item.id || "",
        locationId: item.locationId || item.location_id || "",
        name: item.filename || item.name || "",
        type: item.type || "image",
        format: item.format || "",
        url: item.url || "",
        previewUrl: item.previewUrl || item.preview_url || "",
        browseUrl: item.browseUrl || item.browse_url || item.previewUrl || item.preview_url || item.url || "",
        alternateText: item.altText || item.alt_text || item.alternateText || item.alternate_text || "",
        caption: item.caption || "",
        watermarkText: item.watermarkText || "",
        tags: item.tags || [],
        size: item.size || 0,
        variations: item.variations || {},
        height: item.height || 0,
        width: item.width || 0,
        source: item.source || this.config.locationSource || "",
      };
    },

    normalizeSelectedImage(image) {
      const selectedImage = image || this.getEmptyImageState();
      const uploadedResources = Array.isArray(selectedImage.uploadedResources)
        ? selectedImage.uploadedResources
        : [];

      if (uploadedResources.length > 0) {
        const resources = uploadedResources.map((item) => this.normalizeResource(item));
        const firstResource = resources[0];

        return {
          ...this.getEmptyImageState(),
          ...selectedImage,
          ...firstResource,
          selectedVariation: selectedImage.selectedVariation || null,
          cssClass: selectedImage.cssClass || "",
          uploadedResources: resources,
        };
      }

      if (selectedImage.id) {
        const resource = this.normalizeResource(selectedImage);

        return {
          ...this.getEmptyImageState(),
          ...selectedImage,
          ...resource,
          selectedVariation: selectedImage.selectedVariation || null,
          cssClass: selectedImage.cssClass || "",
          uploadedResources: [resource],
        };
      }

      return {
        ...this.getEmptyImageState(),
        ...selectedImage,
        uploadedResources: [],
      };
    },

    setResources(resources) {
      if (resources.length === 0) {
        this.localSelectedImage = this.getEmptyImageState();
        return;
      }

      const normalizedResources = resources.map((item) => this.normalizeResource(item));
      const firstResource = normalizedResources[0];

      this.localSelectedImage = {
        ...this.localSelectedImage,
        ...firstResource,
        selectedVariation: this.localSelectedImage.selectedVariation || null,
        cssClass: this.localSelectedImage.cssClass || "",
        uploadedResources: normalizedResources,
      };
    },

    dispatchVanillaChangeEvent(inputField = "modal") {
      this.$nextTick(function() {
        this.$el.dispatchEvent(
          new CustomEvent("ngrm-change", {
            detail: {
              inputFields: this.config.inputFields,
              selectedImage: this.localSelectedImage,
              fieldId: this.fieldId,
              changedField: inputField,
              config: this.config,
            },
            bubbles: true,
          })
        );
      });
    },
    prepareDomForModal() {
      const query = document.querySelector(".ez-page-builder-wrapper");
      if (query) {
        query.style.transform = "none";
      }
    },
    resetDomAfterModal() {
      const query = document.querySelector(".ez-page-builder-wrapper");
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
    handleMediaSelected(item) {
      // Enforce upload limit (uploadLimit === 0 means unlimited; uploadLimit === 1 replaces).
      if (this.uploadLimit > 1 && this.currentFileCount >= this.uploadLimit) {
        const template = this.config.translations.limit_reached || 'File limit reached (%limit% maximum)';
        this.showLimitNotice(template.replace('%limit%', this.uploadLimit));
        this.mediaModalOpen = false;
        this.resetDomAfterModal();
        return;
      }

      const newResource = this.normalizeResource(item);
      const allResources =
        this.uploadLimit === 1 ? [newResource] : [...this.resources, newResource];

      this.setResources(allResources);

      this.mediaModalOpen = false;
      this.resetDomAfterModal();
      this.dispatchVanillaChangeEvent();
    },
    showLimitNotice(message) {
      this.limitNotice = message;
      if (this.limitNoticeTimer) clearTimeout(this.limitNoticeTimer);
      this.limitNoticeTimer = setTimeout(() => { this.limitNotice = null; }, 5000);
    },
    handleVariationCropChange(newValues) {
      const resources = [...this.resources];
      const target = resources[this.cropTargetIndex] || this.localSelectedImage;
      const updatedTarget = {
        ...target,
        variations: {
          ...(target.variations || {}),
          ...newValues,
        },
      };

      if (resources[this.cropTargetIndex]) {
        resources.splice(this.cropTargetIndex, 1, updatedTarget);
        this.setResources(resources);
      } else {
        this.localSelectedImage = updatedTarget;
      }

      this.dispatchVanillaChangeEvent();
    },
    handleCropClicked(index = 0) {
      this.cropTargetIndex = index;
      this.cropModalOpen = true;
      this.prepareDomForModal();
    },
    async fetchFacets() {
      this.facetsLoading = true;

      try {
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
      } catch (error) {
        this.types = this.config.allowedTypes.map((id) => ({ id, name: id }));
        this.tags = this.config.allowedTags.map((id) => ({ id, name: id }));
        this.visibilities = this.config.allowedVisibilities.map((id) => ({ id, name: id }));
      } finally {
        this.facetsLoading = false;
      }
    },
    async handleBrowseMediaClicked() {
      this.mediaModalOpen = true;
      this.prepareDomForModal();
      this.fetchFacets();
    },
    handleMultiResourcesUploaded(resources) {
      if (resources.length > 0) {
        const uploadedResources = resources.map((item) => this.normalizeResource(item));
        const allResources =
          this.uploadLimit === 1
            ? (uploadedResources.length > 0 ? [uploadedResources[0]] : [])
            : [...this.resources, ...uploadedResources];

        this.setResources(allResources);
      }

      this.dispatchVanillaChangeEvent();
    },
  },
  watch: {
    selectedImage: function() {
      this.localSelectedImage = this.normalizeSelectedImage(this.selectedImage);
      this.$emit("selectedImageChanged", this.localSelectedImage);
    },
  },
  mounted() {
    this.normalizeSelectedImageForCollectionMode();

    this.$nextTick(function() {
      const modalPortal = document.querySelector(
        `.ngrm-model-portal-${this.fieldId}`
      );

      document.body.prepend(modalPortal);
    });

    // Fetch facets on mount (always needed for browse functionality)
    this.fetchFacets();
  },
  beforeDestroy() {
    if (this.limitNoticeTimer) clearTimeout(this.limitNoticeTimer);
  },
};
</script>
