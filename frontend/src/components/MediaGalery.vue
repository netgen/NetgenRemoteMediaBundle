<template>
  <div class="mediaGalery">
    <div class="items">
      <div v-if="!media.length">Folder is empty. Upload media from your local storage.</div>
      <div class="media" v-for="item in media" :key="item.id" :class="{selected: item.resourceId === selectedMediaId}">
        <div v-if="item.type==='image'">

          <img :src="item.url" :alt="item.filename" class="img"/>
          <Label class="filename">{{item.filename}}</Label>
          <div class="size-description">{{item.width}} x {{item.height}}</div>
        </div>
        <div v-else>
          <span class="video-icon"></span>
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
  overflow-y: auto;
  margin-bottom: 50px;

  .items {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    padding: 15px;

    .media {
      display: flex;
      flex-direction: column;
      justify-content: flex-end;
      width: 177px;
      min-height: 182px;
      background-color: $white;
      padding: 8px;
      margin: 0 15px 15px 0;

      .img {
        display: block;
        margin-bottom: 8px;
        object-fit: cover;
        height: 92px;
        width: 100%;
      }

      .video-icon {
        position: relative;
        height: 92px;
        display: block;

        &:before,
        &:after {
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

        &:after {
          top: 50%;
          left: 50%;
          transform: translate(-50%, -50%);
          background-image: url('data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" width="32" height="32" viewBox="0 0 512 512"%3E%3Cg%3E%3C/g%3E%3Cpath d="M152.443 136.417l207.114 119.573-207.114 119.593z" fill="%23FFF"/%3E%3C/svg%3E');
          background-position: center center;
          width: 32px;
          height: 32px;
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
    left: 361px;
    right: 0;
  }
}
</style>
