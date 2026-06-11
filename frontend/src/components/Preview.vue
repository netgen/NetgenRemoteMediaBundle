<template>
  <!-- Always use array view when uploadedResources exists -->
  <div
    v-if="
      selectedImage.uploadedResources &&
        selectedImage.uploadedResources.length > 0
    "
    class="ngremotemedia-multi-gallery"
  >
    <!-- Canonical wire format in collection mode: a single JSON payload field -->
    <input
      v-if="isCollectionMode"
      type="hidden"
      :name="config.inputFields.collectionPayload"
      :value="collectionPayloadJson"
    />
    <draggable
      :value="resources"
      @input="handleDraggableInput"
      :options="{ handle: '.drag-handle', animation: 200 }"
      :disabled="resources.length <= 1"
      class="gallery-draggable-container"
    >
    <div
      v-for="(resource, index) in selectedImage.uploadedResources"
      :key="resource.uid || resource.id || index"
      class="ngremotemedia-image"
    >
      <!-- Drag handle and position indicator - only show when multiple items -->
      <div class="image-header" v-if="selectedImage.uploadedResources.length > 1">
        <span class="drag-handle" :title="config.translations.reorder_drag || 'Drag to reorder'">
          <i class="fa fa-bars"></i>
        </span>
        <span class="position-indicator">{{ index + 1 }}</span>
        <!-- Reorder buttons for accessibility -->
        <div class="reorder-buttons">
          <button
            type="button"
            class="btn btn-sm btn-default reorder-btn"
            :disabled="index === 0"
            @click="$emit('move-resource', { from: index, to: index - 1 })"
            :title="config.translations.reorder_move_previous || 'Move to previous position'"
          >
            <i class="fa fa-arrow-left"></i>
          </button>
          <button
            type="button"
            class="btn btn-sm btn-default reorder-btn"
            :disabled="index === selectedImage.uploadedResources.length - 1"
            @click="$emit('move-resource', { from: index, to: index + 1 })"
            :title="config.translations.reorder_move_next || 'Move to next position'"
          >
            <i class="fa fa-arrow-right"></i>
          </button>
        </div>
      </div>

      <div class="image-wrap">
        <img
          v-if="resource.type === 'image'"
          :src="getResourceImageUrl(resource)"
          @error="handleResourceImageError($event, resource)"
        />
        <video
          v-else-if="resource.type === 'video'"
          :src="resource.previewUrl"
          controls
        ></video>
        <span v-else class="file-placeholder">
          <span class="icon-doc">
            <i class="fa fa-file"></i>
          </span>
        </span>
      </div>

      <div class="image-meta">
        <!-- Single mode posts the classic named fields; collection mode relies on collectionPayload -->
        <template v-if="!isCollectionMode">
          <input
            type="hidden"
            :name="getInputName('locationId', index)"
            :value="resource.locationId || ''"
          />
          <input
            type="hidden"
            :name="getInputName('type', index)"
            :value="resource.type"
          />
          <input
            type="hidden"
            :name="getInputName('remoteId', index)"
            :value="resource.id"
          />
          <input
            type="hidden"
            :name="getInputName('cropSettings', index)"
            :value="JSON.stringify(resource.variations || {})"
          />
          <input
            type="hidden"
            :name="getInputName('source', index)"
            :value="resource.source || config.locationSource || ''"
          />
        </template>

        <h3 class="title">{{ resource.name }}</h3>
        <p>
          {{ config.translations.preview_size }}:
          {{ formatSize(resource.size) }}
        </p>
        <p>{{ resource.type }} / {{ resource.format }}</p>

        <div class="image-meta-data">
          <!-- Alt Text -->
          <div class="ngremotemedia-alttext">
            <span class="help-block description">
              {{ config.translations.preview_alternate_text }}
            </span>
            <input
              type="text"
              :name="getInputName('altText', index)"
              :value="resource.alternateText"
              @input="updateResource(index, 'alternateText', $event.target.value)"
              v-debounce:500ms="() => dispatchChangeEvent(getFieldName('altText', index))"
              class="media-alttext data"
            />
          </div>

          <!-- Caption -->
          <div class="ngremotemedia-caption">
            <span class="help-block description">
              {{ config.translations.preview_caption }}
            </span>
            <input
              type="text"
              :name="getInputName('caption', index)"
              :value="resource.caption"
              @input="updateResource(index, 'caption', $event.target.value)"
              v-debounce:500ms="() => dispatchChangeEvent(getFieldName('caption', index))"
              class="media-caption data"
            />
          </div>

          <!-- Tags -->
          <div class="ngremotemedia-tags">
            <span class="help-block description">
              {{ config.translations.preview_tags }}
            </span>
            <v-select
              :options="config.allowedTags.length > 0 ? config.allowedTags : allTags"
              :taggable="config.allowedTags.length === 0"
              :multiple="true"
              :value="resource.tags"
              @input="handleMultiTagsInput(index, $event)"
            ></v-select>
            <select
              multiple
              hidden
              :name="getTagsInputName(index)"
              class="ngremotemedia-newtags"
            >
              <option
                v-for="tag in resource.tags"
                :key="tag"
                :value="tag"
                selected
                >{{ tag }}</option
              >
            </select>
          </div>

          <!-- Watermark Text -->
          <div class="ngremotemedia-watermark-text">
            <span class="help-block description">
              {{ config.translations.preview_watermark_text }}
            </span>
            <input
              type="text"
              :name="getInputName('watermarkText', index)"
              :value="resource.watermarkText"
              @input="updateResource(index, 'watermarkText', $event.target.value)"
              v-debounce:500ms="() => dispatchChangeEvent(getFieldName('watermarkText', index))"
              class="media-watermarktext data"
            />
          </div>
        </div>

        <!-- Remove button -->
        <div class="image-actions">
          <button
            v-if="isCroppableResource(resource)"
            type="button"
            class="btn btn-default btn-sm crop-file-btn"
            @click="$emit('crop-resource', index)"
          >
            <i class="fa fa-crop"></i>
            {{ config.translations.interactions_scale }}
          </button>
          <button
            type="button"
            class="btn btn-danger btn-sm remove-file-btn"
            @click="$emit('remove-resource', index)"
            :title="config.translations.multi_gallery_remove || 'Remove'"
          >
            <i class="fa fa-trash"></i>
            {{ config.translations.multi_gallery_remove || 'Remove' }}
          </button>
        </div>
      </div>
    </div>
    </draggable>
  </div>

  <div v-else>
    <i>{{ this.config.translations.interactions_no_media_selected }}</i>
  </div>
</template>

<script>
import { formatByteSize } from "../utility/utility";
import vSelect from "vue-select";
import draggable from "vuedraggable";

export default {
  name: "Preview",
  props: {
    config: Object,
    fieldId: String,
    selectedImage: Object,
    isCroppable: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      allTags: [],
    };
  },
  components: {
    "v-select": vSelect,
    draggable,
  },
  computed: {
    resources() {
      return this.selectedImage.uploadedResources || [];
    },
    isCollectionMode() {
      if (this.config.isCollection !== undefined && this.config.isCollection !== null) {
        return !!this.config.isCollection;
      }

      return Number(this.config.uploadLimit || 0) !== 1;
    },
    collectionFieldName() {
      return this.config.inputFields._collection || this.fieldId;
    },
    collectionPayloadJson() {
      return JSON.stringify(
        this.resources.map((resource) => ({
          locationId: resource.locationId || "",
          remoteId: resource.id || "",
          type: resource.type || "",
          altText: resource.alternateText || "",
          caption: resource.caption || "",
          watermarkText: resource.watermarkText || "",
          tags: resource.tags || [],
          cropSettings: JSON.stringify(resource.variations || {}),
          source: resource.source || this.config.locationSource || "",
        }))
      );
    },
  },
  methods: {
    handleDraggableInput(newList) {
      this.$emit('reorder-resources', newList);
    },
    updateResource(index, field, value) {
      this.$emit('update-resource', { index, field, value });
    },
    formatSize(bytes) {
      return formatByteSize(bytes);
    },
    getResourceImageUrl(resource) {
      return resource.url || resource.previewUrl;
    },
    handleResourceImageError(event, resource) {
      if (!resource.previewUrl || event.target.dataset.previewFallback === "1") {
        return;
      }

      event.target.dataset.previewFallback = "1";
      event.target.src = resource.previewUrl;
    },
    // Label used in change events; stable in both modes.
    getFieldName(fieldKey, index) {
      if (this.isCollectionMode) {
        return `${this.collectionFieldName}[${index}][${fieldKey}]`;
      }

      return this.config.inputFields[fieldKey] || fieldKey;
    },
    getTagsFieldName(index) {
      if (this.isCollectionMode) {
        return `${this.collectionFieldName}[${index}][tags][]`;
      }

      return this.config.inputFields.tags;
    },
    // Form input name; null in collection mode so only collectionPayload is submitted.
    getInputName(fieldKey, index) {
      if (this.isCollectionMode) {
        return null;
      }

      return this.getFieldName(fieldKey, index);
    },
    getTagsInputName(index) {
      if (this.isCollectionMode) {
        return null;
      }

      return this.getTagsFieldName(index);
    },
    isCroppableResource(resource) {
      return this.isCroppable && resource.type === "image";
    },
    handleMultiTagsInput(index, value) {
      this.allTags = [...new Set([...this.allTags, ...value])];
      this.updateResource(index, 'tags', value);
      this.dispatchChangeEvent(this.getTagsFieldName(index));
    },
    dispatchChangeEvent(inputField) {
      this.$emit("preview-change", inputField);
    },
    collectTags() {
      return [
        ...new Set(
          this.resources.reduce(
            (tags, resource) => tags.concat(resource.tags || []),
            []
          )
        ),
      ];
    },
  },
  mounted() {
    this.allTags = this.collectTags();
  },
  watch: {
    selectedImage: function() {
      this.allTags = this.collectTags();
    },
  },
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped lang="scss">
@import "../scss/variables";

// Multi-gallery layout for multiple uploaded images
.ngremotemedia-multi-gallery {
  margin-bottom: 20px;
  max-width: 100%;
  overflow: hidden;

  *,
  *:before,
  *:after {
    box-sizing: border-box;
  }

  .gallery-draggable-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(420px, 480px));
    gap: 24px;
    align-items: start;
    justify-content: start;
  }

  .ngremotemedia-image {
    display: block;
    min-width: 0;
    width: 100%;
    max-width: 100%;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 15px;
    background-color: #fff;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: box-shadow 0.2s, transform 0.2s;

    &.sortable-ghost {
      opacity: 0.5;
      background-color: #f0f8ff;
    }

    &.sortable-drag {
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
      transform: rotate(2deg);
    }

    .image-header {
      display: flex;
      align-items: center;
      margin-bottom: 10px;
      padding-bottom: 10px;
      border-bottom: 1px solid #eee;
      min-width: 0;

      .drag-handle {
        flex: 0 0 auto;
        cursor: grab;
        padding: 8px;
        color: #999;
        font-size: 16px;
        border-radius: 4px;
        transition: color 0.2s, background-color 0.2s;

        &:hover {
          color: #333;
          background-color: #f5f5f5;
        }

        &:active {
          cursor: grabbing;
        }
      }

      .position-indicator {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        background-color: #4a90e2;
        color: white;
        border-radius: 50%;
        font-size: 12px;
        font-weight: 600;
        margin-left: 8px;
      }

      .reorder-buttons {
        margin-left: auto;
        display: flex;
        gap: 4px;
        flex: 0 0 auto;

        .reorder-btn {
          width: 32px;
          height: 32px;
          padding: 0;
          display: flex;
          align-items: center;
          justify-content: center;
          border: 1px solid #ddd;
          background-color: #fff;
          border-radius: 4px;
          cursor: pointer;
          transition: all 0.2s;

          &:hover:not(:disabled) {
            background-color: #f5f5f5;
            border-color: #4a90e2;
            color: #4a90e2;
          }

          &:disabled {
            opacity: 0.3;
            cursor: not-allowed;
          }

          i {
            font-size: 12px;
          }
        }
      }
    }

    .image-wrap {
      display: flex;
      margin-bottom: 15px;
      width: 100%;
      max-width: 100%;
      float: none;
      overflow: hidden;
      border-radius: 4px;
      height: 360px;
      padding: 24px;
      background-color: #f5f5f5;
      align-items: center;
      justify-content: center;

      img,
      video {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: contain;
        border-radius: 4px;
      }

      .file-placeholder {
        position: relative;
        width: 100%;
        height: 220px;
        display: block;
        background-color: #f5f5f5;
        border-radius: 4px;

        .icon-doc {
          position: absolute;
          top: 50%;
          left: 50%;
          transform: translate(-50%, -50%);
          color: #999;
          font-size: 40px;
        }

        &:before {
          position: absolute;
          content: "";
        }

        &:before {
          background-color: rgba(0, 0, 0, 0.1);
          top: 0;
          bottom: 0;
          left: 0;
          right: 0;
          border-radius: 4px;
        }
      }
    }

    .image-meta {
      display: block;
      min-width: 0;
      width: 100%;
      max-width: 100%;
      float: none;

      .title {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 8px;
        color: #333;
        overflow-wrap: anywhere;
      }

      p {
        font-size: 13px;
        color: #666;
        margin-bottom: 4px;
      }

      .image-meta-data {
        display: block;
        margin-top: 15px;

        > div {
          display: block;
          min-width: 0;
          width: 100%;
          max-width: 100%;
          margin-bottom: 15px;

          .help-block.description {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #555;
            margin-bottom: 5px;
          }

          input[type="text"] {
            width: 100%;
            min-width: 0;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 3px;
            font-size: 13px;

            &:focus {
              outline: none;
              border-color: #4a90e2;
              box-shadow: 0 0 0 2px rgba(74, 144, 226, 0.1);
            }
          }
        }

        .ngremotemedia-tags {
          .v-select {
            width: 100%;
            min-width: 0;
          }
        }

        > div:last-child {
          margin-bottom: 0;
        }
      }
    }

    .image-actions {
      display: block;
      width: 100%;
      max-width: 100%;
      float: none;
      margin-top: 15px;
      padding-top: 15px;
      border-top: 1px solid #ddd;

      .crop-file-btn,
      .remove-file-btn {
        width: 100%;
        padding: 8px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 13px;
        margin-bottom: 8px;
      }

      .crop-file-btn {
        background-color: #f5f5f5;
        color: #333;

        &:hover {
          background-color: #e8e8e8;
        }
      }

      .remove-file-btn {
        background-color: #dc3545;
        color: white;
        margin-bottom: 0;

        &:hover {
          background-color: #c82333;
        }

        i {
          margin-right: 5px;
        }
      }
    }
  }

  @media (max-width: 520px) {
    .gallery-draggable-container {
      grid-template-columns: 1fr;
    }

    .ngremotemedia-image {
      padding: 12px;
    }
  }
}

// Single image layout (existing)
.ngremotemedia-image {
  .image-wrap {
    .file-placeholder {
      position: relative;
      max-width: 500px;
      height: 280px;
      display: block;
      margin-bottom: 4px;

      .icon-doc {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: $white;
        font-size: 40px;
      }

      &:before {
        position: absolute;
        content: "";
      }

      &:before {
        background-color: rgba(0, 0, 0, 0.7);
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
      }
    }
  }
}
</style>
