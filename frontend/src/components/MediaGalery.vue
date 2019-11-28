<template>
  <div class="mediaGalery">
    <div class="items">
      <div v-if="!media.length" class="folder-empty">
        <span class="icon-folder"></span>
        <span><strong>Folder is empty</strong>Upload media from your local storage.</span>
      </div>
      <div class="media" v-for="item in media" :key="item.id" :class="{selected: item.resourceId === selectedMediaId}">
        <div v-if="item.type==='image'" class="media-container">
          <img :src="item.browse_url" :alt="item.filename" class="img"/>
          <Label class="filename">{{item.filename}}</Label>
          <div class="size-description">{{item.width}} x {{item.height}}</div>
        </div>
        <div v-else class="media-container">
          <span class="video-placeholder">
            <span class="icon-play"></span>
          </span>
          <Label class="filename">{{item.filename}}</Label>
          <div class="size-description">{{item.width}} x {{item.height}}</div>
        </div>
        <button type="button" @click="$emit('media-selected', item)" class="btn btn-blue select-btn">Select</button>
      </div>
    </div>

    <div class="load-more-wrapper" v-if="canLoadMore">
      <button type="button" class="btn btn-blue" @click="$emit('loadMore')">Load more</button>
    </div>
  </div>
</template>

<script>
export default {
  name: "MediaGalery",
  props: ["media", "canLoadMore", "onLoadMore", "selectedMediaId"]
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
        margin-bottom: 8px;
        object-fit: cover;
        height: 92px;
        width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
      }

      .video-placeholder {
        position: relative;
        height: 92px;
        display: block;

        .icon-play {
          position: absolute;
          top: 50%;
          left: 50%;
          transform: translate(-50%, -50%);
          color: $white;
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
      }

      .size-description {
        font-size: 12px;
        line-height: 14px;
        text-align: center;
        color: $dusty-gray;
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

      &.icon-folder {
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
