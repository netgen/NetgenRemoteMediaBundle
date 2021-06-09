<template>
  <div class="overlay">
    <div class="media-modal">
      <div class="title">
        {{title}}
        <span @click="close" class="close">
          <span class="ngrm-icon-cancel"></span>
        </span>
      </div>
      <div class="body">
        <slot></slot>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "Modal",
  props: ["title"],
  methods: {
    close() {
      this.$emit("close");
    }
  }
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped lang="scss">
@import "../scss/variables";

.overlay {
  position: fixed;
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;
  background-color: rgba(0, 0, 0, .8);
  z-index: 11;

  .media-modal {
    background-color: $wild-sand;
    box-shadow: 0 5px 15px 0 rgba(0, 0, 0, 0.5);
    margin: 32px;
    height: calc(100% - 64px);

    .title {
      padding: 15px;
      font-size: 16px;
      font-weight: 700;
      line-height: 20px;
      color: $mine-shaft;
      background: $white;
      text-transform: uppercase;
      box-shadow: inset 0 -1px 0 0 $mercury;

      .close {
        float: right;
        cursor: pointer;
        padding: 2px 10px;
      }
    }

    .body {
      display: flex;
      flex-direction: row;
      position: relative;
      height: calc(100% - 50px);
    }
  }
}
</style>
