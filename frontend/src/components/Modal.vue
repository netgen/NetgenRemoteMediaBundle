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

>

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

<style lang="scss">
@import "../scss/variables";
// Specific targeted styles for Sylius admin version
.sidebar + div.menu + .pusher {
  #wrapper {
    .ngrm-overlay {
      top: 48px;
      width: 100%;
      padding: 32px;
      top: 48px;
      transition: all .5s ease;
    }
  }
}

.sidebar.visible + div.menu + .pusher {
  #wrapper {
    .ngrm-overlay {
      width: calc(100% - 260px);
    }
  }
}

.sidebar.uncover.visible + div.menu + .pusher {
  #wrapper {
    .ngrm-overlay {
      width: calc(100% - 260px);
    }
  }
}

.vue-treeselect__input-container input,
.vs__selected-options input {
  border: none!important;
}

.search-wrapper .search input {
  border-radius: 0!important;
}

.ngremotemedia-container {
  .v-select .vs__dropdown-toggle {
    padding: 0;
  }

  .sidebar-crop {
  button.btn {
      font-size: 14px!important;
      font-family: Arial;
    }
  }

  .action-strip {
    button.btn {
      font-size: 14px!important;
      font-family: Arial;
    }
  }
}

.media .btn.select-btn,
.load-more-wrapper .btn,
.upload-from-disk label {
    font-size: 14px!important;
    font-family: Arial;
}

.vs__dropdown-toggle .vs__selected-options {
  input[type="search"] {
    padding: 3px 7px;
  }
}

.ngremotemedia-image {
  h3.title {
      word-break: break-word;
  }

  .ngremotemedia-tags {
    .ngremotemedia-newtags {
      display: none;
    }
  }
}

#form {
  label.required {
    display: block;
    margin: 0.5rem 0 0.5rem 0;
    font-weight: 400;
    font-family: 'Lato', 'Helvetica Neue', Arial, Helvetica, sans-serif;
    font-size: 0.92rem;
    color: rgba(0, 0, 0, 0.8);

    &::after {
      margin: -0.2em 0em 0em 0.2em;
      content: '*';
      color: $crimson;
    }
  }
}

.ngremotemedia-buttons {
  input[type="button"] {
      cursor: pointer;
      display: inline-block;
      min-height: 1em;
      outline: none;
      border: none;
      vertical-align: baseline;
      background: #E0E1E2 none;
      color: rgba(0, 0, 0, 0.6);
      font-family: 'Lato', 'Helvetica Neue', Arial, Helvetica, sans-serif;
      margin-bottom: 4px;
      margin-right: 4px;
      padding: 0.78571429em 1.5em 0.78571429em;
      text-transform: none;
      text-shadow: none;
      font-weight: bold;
      line-height: 1em;
      font-style: normal;
      text-align: center;
      text-decoration: none;
      border-radius: 0.28571429rem;
      box-shadow: 0px 0px 0px 1px transparent inset, 0px 0em 0px 0px rgba(34, 36, 38, 0.15) inset;
      user-select: none;
      transition: opacity 0.1s ease, background-color 0.1s ease, color 0.1s ease, box-shadow 0.1s ease, background 0.1s ease;
      will-change: '';
      -webkit-tap-highlight-color: transparent;
  }
}

.input-file-name-wrapper {
  display: flex;
  align-items: center;

  input[type=text], .v-select {
    margin-right: 32px;
  }

  button.btn {
    margin-left: 12px;
  }
}
</style>