<template>
  <div class="overlay">
    <div class="media-modal">
      <div class="title">
        Select media
        <span @click="close" class="close">&#x00D7;</span>
      </div>
      <div class="body">
        <media-facets :folders="folders" :facets="facets" @change="handleFacetsChange" />
        <media-galery :media="media" :canLoadMore="canLoadMore" @loadMore="handleLoadMore" />
      </div>
    </div>
  </div>
</template>

<script>
import MediaFacets from "./MediaFacets";
import MediaGalery from "./MediaGalery";
import { FOLDER_ALL, SEARCH_NAME, TYPE_IMAGE } from "../constants/facets";
import { encodeQueryData } from "../utility/utility";
import debounce from "debounce";

const NUMBER_OF_ITEMS = 25;

export default {
  name: "MediaModal",
  props: ["folders"],
  components: {
    "media-facets": MediaFacets,
    "media-galery": MediaGalery
  },
  data() {
    return {
      media: [],
      canLoadMore: false,
      facets: {
        folder: "",
        searchType: SEARCH_NAME,
        mediaType: TYPE_IMAGE,
        query: ""
      }
    };
  },
  methods: {
    close() {
      this.$emit("close");
    },
    debouncedLoad: debounce(function(options) {
      this.load(options);
    }, 500),
    async load({ patch } = { patch: false }) {
      this.abortController && this.abortController.abort();
      this.abortController = new AbortController();

      const query = {
        limit: NUMBER_OF_ITEMS,
        offset: patch ? this.media.length : 0,
        q: this.facets.query,
        mediatype: this.facets.mediaType,
        folder: this.facets.folder || FOLDER_ALL,
        search_type: this.facets.searchType
      };

      const url = `/ngadminui/ngremotemedia/browse?${encodeQueryData(query)}`;

      try {
        const response = await fetch(url, {
          signal: this.abortController.signal
        });
        const media = await response.json();

        this.media = patch ? this.media.concat(media.hits) : media.hits;
        this.canLoadMore = media.load_more;
      } catch (err) {
        //user aborted request
        if (err.code !== 20) {
          throw err;
        }
      }
    },
    handleLoadMore() {
      this.debouncedLoad({ patch: true });
    },
    handleFacetsChange(change) {
      this.facets = {
        ...this.facets,
        ...change
      };

      this.debouncedLoad();
    }
  },
  mounted() {
    this.load();
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
    background-color: white;
    margin: 20px;
    height: 97%;
    overflow: scroll;

    .title {
      padding: 20px;
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
