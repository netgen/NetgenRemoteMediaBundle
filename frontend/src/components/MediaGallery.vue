<template>
  <div class="media-gallery">
    <div :class="loading ? 'items loading' : 'items'">
      <div v-if="!media.length" class="folder-empty">
        <span class="ngrm-icon-folder"></span>
        <span><strong>{{ this.translations.media_gallery_empty_folder }}</strong>{{ this.translations.media_gallery_upload_media }}</span>
      </div>
      <div class="media" 
           v-for="item in media" 
           :key="getResourceId(item)" 
           :class="{selected: multiSelect ? isSelected(getResourceId(item)) : getResourceId(item) === selectedMediaId}">
        
        <div v-if="hasPreviewImage(item)" class="media-container">
          <img :src="getPreviewImageUrl(item)" :alt="getFilename(item)" class="img"/>
          <Label class="filename">{{getFilename(item)}}</Label>
          <div class="size-description">
            <i v-if="item.visibility === 'public'" class="fa fa-solid fa-globe">&nbsp;&nbsp;</i>
            <i v-if="item.visibility === 'private'" class="fa fa-eye-slash">&nbsp;&nbsp;</i>
            <i v-if="item.visibility === 'protected'" class="fa fa-lock">&nbsp;&nbsp;</i>
            <span class="format">{{item.format}}</span> - {{item.width}} x {{item.height}} - {{showFilesize(item)}}
          </div>
        </div>
        <div v-else class="media-container">
          <span class="file-placeholder">
            <span class="icon-doc">
                <i v-if="item.format==='pdf'" class="fa fa-file-pdf-o"></i>
                <i v-else-if="item.format==='zip' || item.format==='rar'" class="fa fa-file-archive-o"></i>
                <i v-else-if="item.format==='ppt' || item.format==='pptx'" class="fa fa-file-powerpoint-o"></i>
                <i v-else-if="item.format==='doc' || item.format==='docx'" class="fa fa-file-word-o"></i>
                <i v-else-if="item.format==='xls' || item.format==='xlsx'" class="fa fa-file-excel-o"></i>
                <i v-else-if="item.format==='aac' || item.format==='aiff' || item.format==='amr' || item.format==='flac'|| item.format==='m4a'
                || item.format==='mp3' || item.format==='ogg' || item.format==='opus' || item.format==='wav'" class="fa fa-file-audio-o"></i>
                <i v-else-if="item.format==='txt'" class="fa fa-lg fa-file-text"></i>
                <i v-else class="fa fa-file"></i>
            </span>
          </span>
          <Label class="filename">{{getFilename(item)}}</Label>
          <div class="size-description">
            <i v-if="item.visibility === 'public'" class="fa fa-solid fa-globe">&nbsp;&nbsp;</i>
            <i v-if="item.visibility === 'private'" class="fa fa-eye-slash">&nbsp;&nbsp;</i>
            <i v-if="item.visibility === 'protected'" class="fa fa-lock">&nbsp;&nbsp;</i>
            <span class="format">{{item.format}}</span> - {{showFilesize(item)}}
          </div>
        </div>
        <button type="button" 
                @click="handleSelectClick(item)" 
                class="btn btn-blue select-btn"
                :disabled="multiSelect && !canSelectMore && !isSelected(getResourceId(item))">
          {{ multiSelect 
             ? (isSelected(getResourceId(item)) ? translations.multi_select_deselect : translations.media_gallery_select) 
             : translations.media_gallery_select }}
        </button>
      </div>
    </div>

    <!-- Transient banner for blocked selection attempts -->
    <div v-if="notice"
         class="limit-indicator limit-exceeded"
         role="alert">
      <i class="fa fa-exclamation-triangle"></i>
      <span>{{ notice }}</span>
    </div>

    <!-- Multi-select confirmation bar -->
    <div v-if="multiSelect" class="multi-select-bar">
      <div class="selection-info">
        {{ selectionMessage }}
      </div>
      <button type="button"
              class="btn btn-primary"
              :disabled="selectedItems.length === 0"
              @click="confirmMultiSelection">
        {{ (translations.multi_select_add_selected || 'Add selected (%count%)').replace('%count%', selectedItems.length) }}
      </button>
    </div>
    
    <div class="load-more-wrapper" v-if="canLoadMore">
      <button type="button" class="btn btn-blue" @click="$emit('loadMore')">{{ this.translations.media_gallery_load_more }}</button>
    </div>
  </div>
</template>

<script>
import prettyBytes from "pretty-bytes";

export default {
  name: "MediaGallery",
  props: {
    translations: Object,
    media: Array,
    canLoadMore: Boolean,
    onLoadMore: Function,
    selectedMediaId: String,
    loading: Boolean,
    multiSelect: {
      type: Boolean,
      default: false
    },
    selectionLimit: {
      type: Number,
      default: 0  // 0 = unlimited
    },
    currentCount: {
      type: Number,
      default: 0
    }
  },
  data() {
    return {
      selectedItems: [],
      notice: null,
      noticeTimer: null,
    };
  },
  computed: {
    hasSelectionLimit() {
      return this.selectionLimit > 0;
    },
    remainingSlots() {
      if (!this.hasSelectionLimit) return Infinity;
      return Math.max(0, this.selectionLimit - this.currentCount);
    },
    canSelectMore() {
      return !this.hasSelectionLimit || this.selectedItems.length < this.remainingSlots;
    },
    selectionMessage() {
      if (!this.multiSelect) return '';
      if (!this.hasSelectionLimit) {
        return `${this.selectedItems.length} item(s) selected`;
      }
      const remaining = this.remainingSlots - this.selectedItems.length;
      if (remaining <= 0) {
        return `Selection limit reached (${this.remainingSlots} maximum)`;
      }
      return `${this.selectedItems.length} selected, ${remaining} slot(s) remaining`;
    }
  },
  methods: {
    showFilesize(item) {
      return prettyBytes(item.size);
    },
    getResourceId(item) {
      return item.remoteId || item.remote_id || item.id || "";
    },
    getFilename(item) {
      return item.filename || item.name || "";
    },
    getPreviewImageUrl(item) {
      return item.browseUrl || item.browse_url || item.previewUrl || item.preview_url || item.url || "";
    },
    hasPreviewImage(item) {
      return (item.type === "image" || item.type === "video") && this.getPreviewImageUrl(item) !== "";
    },
    isSelected(remoteId) {
      return this.selectedItems.some(item => this.getResourceId(item) === remoteId);
    },
    toggleSelection(item) {
      const itemId = this.getResourceId(item);
      const index = this.selectedItems.findIndex(i => this.getResourceId(i) === itemId);
      if (index > -1) {
        // Deselect
        this.selectedItems.splice(index, 1);
      } else {
        // Select if under limit
        if (this.canSelectMore) {
          this.selectedItems.push(item);
        } else {
          const template = this.translations.limit_reached || 'File limit reached (%limit% maximum)';
          this.showNotice(template.replace('%limit%', this.selectionLimit));
        }
      }
    },
    showNotice(message) {
      this.notice = message;
      if (this.noticeTimer) clearTimeout(this.noticeTimer);
      this.noticeTimer = setTimeout(() => { this.notice = null; }, 5000);
    },
    handleSelectClick(item) {
      if (this.multiSelect) {
        this.toggleSelection(item);
      } else {
        this.$emit('media-selected', item);
      }
    },
    confirmMultiSelection() {
      this.$emit('media-multi-selected', this.selectedItems);
      this.selectedItems = [];
    }
  },
  beforeDestroy() {
    if (this.noticeTimer) clearTimeout(this.noticeTimer);
  }
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped lang="scss">
@import "../scss/variables";

.media-gallery {
  position: relative;
  flex-grow: 1;

  .items {
    padding: 15px;
    overflow-y: auto;
    height: calc(100% - 50px);

    &.loading {
      opacity: 0.5;
    }

    .media {
      position: relative;
      width: 190px;
      min-height: 182px;
      max-height: 190px;
      padding: 8px;
      margin: 0 15px 15px 0;
      background-color: $white;

      display: inline-block;

      .media-container {
        width: 100%;
      }

      .img {
        display: block;
        margin-bottom: 4px;
        object-fit: cover;
        height: 100px;
        width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
      }

      .file-placeholder {
        position: relative;
        height: 95px;
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
          content: '';
        }

        &:before {
          background-color: rgba(0, 0, 0, .7);
          top: 0;
          bottom: 0;
          left: 0;
          right: 0;
        }
      }

      .filename {
        overflow: hidden;
        display: inline-block;
        text-overflow: ellipsis;
        white-space: nowrap;
        width: 100%;
        text-align: center;
        font-size: 16px;
        line-height: 20px;
        margin-top: 4px;
        margin-bottom: 0;
      }

      .size-description {
        font-size: 12px;
        line-height: 14px;
        text-align: center;
        color: $dusty-gray;

        .format {
          text-transform: uppercase;
        }
      }

      &.selected {
        border: 1px solid $netgen-primary;
      }

      .select-btn {
        margin-top: 8px;
        padding: 3px;
        width: 100%;
      }
    }
  }

  .folder-empty {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);

    span {
      display: block;
      text-align: center;
      font-size: 14px;
      line-height: 16px;

      &.ngrm-icon-folder {
        color: $dusty-gray;
        font-size: 33px;
      }

      strong {
        display: block;
        margin: 5px 0;
        font-size: 16px;
        line-height: 19px;
      }
    }
  }

  .multi-select-bar {
    position: sticky;
    bottom: 0;
    padding: 15px;
    background-color: #f8f9fa;
    border-top: 2px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: center;
    
    .selection-info {
      font-size: 14px;
      font-weight: 600;
      color: #495057;
    }
    
    .btn-primary {
      padding: 10px 20px;
      background-color: #007bff;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      
      &:disabled {
        opacity: 0.5;
        cursor: not-allowed;
      }
      
      &:hover:not(:disabled) {
        background-color: #0056b3;
      }
    }
  }

  .load-more-wrapper {
    padding: 8px 15px;
    background-color: $white;
    text-align: right;
    box-shadow: inset 1px 0 0 0 $mercury, 0 -1px 0 0 $mercury;
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
  }
}
</style>
