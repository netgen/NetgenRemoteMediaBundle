<template>
  <div class="mediaGalery">
    <div :class="loading ? 'items loading' : 'items'">
      <div v-if="!media.length" class="folder-empty">
        <span class="ngrm-icon-folder"></span>
        <span><strong>{{ this.$root.$data.NgRemoteMediaTranslations.media_galery_empty_folder }}</strong>{{ this.$root.$data.NgRemoteMediaTranslations.media_galery_upload_media }}</span>
      </div>
      <div class="media" v-for="item in media" :key="item.id" :class="{selected: item.resourceId === selectedMediaId}">
        <div v-if="item.mediaType==='image' || (item.mediaType==='video' && item.browse_url!=='')" class="media-container">
          <img :src="item.browse_url" :alt="item.filename" class="img"/>
          <Label class="filename">{{item.filename}}</Label>
          <div class="size-description"><span class="format">{{item.format}}</span> - {{item.width}} x {{item.height}} - {{showFilesize(item)}}</div>
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
          <Label class="filename">{{item.filename}}</Label>
          <div class="size-description"><span class="format">{{item.format}}</span> - {{showFilesize(item)}}</div>
        </div>
        <button type="button" @click="$emit('media-selected', item)" class="btn btn-blue select-btn">{{ _self.$root.$data.NgRemoteMediaTranslations.media_galery_select }}</button>
      </div>
    </div>

    <div class="load-more-wrapper" v-if="canLoadMore">
      <button type="button" class="btn btn-blue" @click="$emit('loadMore')">{{ this.$root.$data.NgRemoteMediaTranslations.media_galery_load_more }}</button>
    </div>
  </div>
</template>

<script>
import prettyBytes from "pretty-bytes";

export default {
  name: "MediaGalery",
  props: ["media", "canLoadMore", "onLoadMore", "selectedMediaId", "loading"],
  methods: {
    showFilesize(item) {
      return prettyBytes(item.filesize);
    },
  }
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped lang="scss">
@import "../scss/variables";

.mediaGalery {
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
      width: 177px;
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
        height: 92px;
        width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
      }

      .file-placeholder {
        position: relative;
        height: 92px;
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
