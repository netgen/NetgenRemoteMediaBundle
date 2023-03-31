<template>
  <div class="ngrm-overlay">
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

.ngrm-overlay {
  position: fixed;
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;
  background-color: rgba(0, 0, 0, .8);
  z-index: 9999;

  .media-modal {
    background-color: $wild-sand;
    box-shadow: 0 5px 15px 0 rgba(0, 0, 0, 0.5);
    margin: 32px;
    height: calc(100vh - 64px);

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

  .img-placeholder {
    img {
      height: 100%;
    }
  }
}
</style>

<style lang="scss">
.ngremotemedia-buttons {
  input[type="button"] {
    margin-bottom: 4px;
    margin-right: 4px;
  }
}

.help-block.description {
  margin-right: 6px;
}

.btn, .sidebar-crop .buttons {
  font-size: 14px!important;
  font-family: Arial, Helvetica, sans-serif;
}

.ngremotemedia-tags {
  .vs__selected-options {
    input[type="search"].vs__search {
      border: none;
    }
  }

  select[hidden="hidden"] {
    display: none;
  }
} 

.ngremotemedia-image {
  h3.title {
      word-break: break-word;
  }

  .image-wrap {
    img {
      width: 100%;
      max-width: 100%;
    }
  }
}

.input-file-name-wrapper {
  display: flex;
  align-items: center;
  input[type=text], .v-select {
    margin-right: 16px;
  }
  .v-select.vs--single.vs--searchable {
    margin-right: 10px;
    input.vs__search {
      background: transparent;
    }
  }
  button.btn {
    margin-left: 40%;
    font-size: 14px!important;
    font-family: Arial;
  }
  .vs__dropdown-toggle {
    height: 37px;
  }
}

.media-gallery .items .media {
  line-height: normal;
}
</style>