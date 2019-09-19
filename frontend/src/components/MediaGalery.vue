<template>
  <div class="mediaGalery">
    <div class="items">
      <div class="media" v-for="item in media" :key="item.id">
        <div v-if="item.type==='image'">
          <img :src="item.url" :alt="item.filename" />
          <Label>{{item.filename}}</Label>
          <div>{{item.width}} x {{item.height}}</div>
        </div>
        <div v-else>
          <i class="ng-icon ng-video" />
          <Label>{{item.filename}}</Label>
          <div>{{item.width}} x {{item.height}}</div>
        </div>
      </div>
    </div>
    <button v-if="canLoadMore" @click="$emit('loadMore')">Load more</button>
  </div>
</template>

<script>
export default {
  name: "MediaGalery",
  props: ["media", "canLoadMore", "onLoadMore"]
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped lang="scss">
.mediaGalery {
  .items {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    .media {
      display: flex;
      width: 10em;
      padding: 1em;
      flex-direction: column;
      overflow: hidden;
    }
  }
}
</style>
