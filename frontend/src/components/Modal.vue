<template>
  <div class="overlay">
    <div class="media-modal">
      <div class="title">
        {{title}}
        <span @click="close" class="close">&#x00D7;</span>
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
.overlay {
  position: fixed;
  top: 0em;
  bottom: 0em;
  left: 0em;
  right: 0em;
  background-color: rgba(0, 0, 0, 0.7);
  z-index: 11;

  .media-modal {
    background-color: #f5f5f5;
    box-shadow: 0 5px 15px 0 rgba(0, 0, 0, 0.5);
    margin: 20px;
    height: 97%;
    overflow: scroll;

    .title {
      padding: 15px;
      font-size: 16px;
      font-weight: 700;
      line-height: 20px;
      color: #333;
      background: #fff;
      text-transform: uppercase;
      box-shadow: inset 0 -1px 0 0 #e4e4e4;

      .close {
        float: right;
      }
    }

    .body {
      display: flex;
      flex-direction: row;
    }
  }
}
</style>
