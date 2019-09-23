<template>
  <div class="mediaGalery">
    <div class="items">
      <div v-if="!media.length">Folder is empty. Upload media from your local storage.</div>
      <div class="media" v-for="item in media" :key="item.id" :class="{selected: item.resourceId === selectedMediaId}">
        <div v-if="item.type==='image'">
          <img :src="item.url" :alt="item.filename" />
          <Label class="filename">{{item.filename}}</Label>
          <div class="wh">{{item.width}} x {{item.height}}</div>
        </div>
        <div v-else>
          <i class="ng-icon ng-video" />
          <Label class="filename">{{item.filename}}</Label>
          <div class="wh">{{item.width}} x {{item.height}}</div>
        </div>
        <button type="button" @click="$emit('media-selected', item)">Select</button>
      </div>
    </div>
    <div class="load-more-wrapper">
      <button type="button" v-if="canLoadMore" @click="$emit('loadMore')">Load more</button>
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
.mediaGalery {
  .items {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    padding: 15px;
    color: #333;

    .media {
      display: flex;
      flex-direction: column;
      justify-content: flex-end;
      width: 177px;
      background-color: #fff;
      padding: 8px;
      margin: 0 15px 15px 0;

      .img {
        width: 100%;
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

      .wh {
        font-size: 12px;
        line-height: 14px;
        text-align: center;
        color: #999;
      }

      &.selected {
        border: 1px black solid;
      }
    }

    .load-more-wrapper {
      padding: 8px;
      background-color: #fff;

      button {
        padding: 8px;
      }
    }
  }
}
</style>
