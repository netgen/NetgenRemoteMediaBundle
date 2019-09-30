<template>
  <div class="mediaGalery">
    <div class="items">
      <div v-if="!media.length">Folder is empty. Upload media from your local storage.</div>
      <div class="media" v-for="item in media" :key="item.id" :class="{selected: item.resourceId === selectedMediaId}">
        <div v-if="item.type==='image'">
          <img :src="item.url" :alt="item.filename" class="img"/>
          <Label class="filename">{{item.filename}}</Label>
          <div class="wh">{{item.width}} x {{item.height}}</div>
        </div>
        <div v-else>
          <i class="ng-icon ng-video" />
          <Label class="filename">{{item.filename}}</Label>
          <div class="wh">{{item.width}} x {{item.height}}</div>
        </div>
        <button type="button" @click="$emit('media-selected', item)" class="btn-select">Select</button>
      </div>
    </div>
    <div class="load-more-wrapper" v-if="canLoadMore">
      <button type="button" @click="$emit('loadMore')">Load more</button>
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
  height: calc(100vh - 88px);
  overflow-y: auto;
  padding-bottom: 50px;

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
      background-color: #fff;
      padding: 8px;
      margin: 0 15px 15px 0;

      .img {
        margin-bottom: 8px;
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

      .btn-select {
        background-color: #009AC7;
        border-radius: 4px;
        text-align: center;
        font-size: 14px;
        line-height: 18px;
        color: #FFF;
        margin-top: 8px;
        padding: 3px;
        border: 0;
      }
    }
  }

  .load-more-wrapper {
    padding: 8px 15px;
    background-color: #FFF;
    text-align: right;
    box-shadow: inset 1px 0 0 0 #E4E4E4, 0 -1px 0 0 #E4E4E4;
    position: absolute;
    bottom: 0;
    left: 361px;
    right: 0;

    button {
      padding: 8px;
      background-color: #009AC7;
      border-radius: 4px;
      color: #FFF;
      margin: 0 15px;
      min-width: 100px;
      border: 0;
      font-size: 14px;
      line-height: 18px;
    }
  }
}
</style>
