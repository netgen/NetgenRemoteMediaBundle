<template>
  <modal title="Select media" @close="$emit('close')">
    <media-facets :folders="folders" :facets="facets" @change="handleFacetsChange" />
    <media-galery
      v-if="!loading"
      :media="media"
      :canLoadMore="canLoadMore"
      :selectedMediaId="selectedMediaId"
      @loadMore="handleLoadMore"
      @media-selected="item => $emit('media-selected', item)"
    />
    <i v-else class="ng-icon ng-spinner" />
  </modal>
</template>

<script>
import MediaFacets from "./MediaFacets";
import MediaGalery from "./MediaGalery";
import { FOLDER_ALL, SEARCH_NAME, TYPE_IMAGE } from "../constants/facets";
import { encodeQueryData } from "../utility/utility";
import debounce from "debounce";
import Modal from "./Modal";

const NUMBER_OF_ITEMS = 25;

export default {
  name: "MediaModal",
  props: ["folders", "selectedMediaId"],
  components: {
    "media-facets": MediaFacets,
    "media-galery": MediaGalery,
    modal: Modal
  },
  data() {
    return {
      media: [],
      canLoadMore: false,
      loading: true,
      facets: {
        folder: "",
        searchType: SEARCH_NAME,
        mediaType: TYPE_IMAGE,
        query: ""
      }
    };
  },
  methods: {
    debouncedLoad: debounce(function(options) {
      this.load(options);
    }, 500),
    async load({ patch } = { patch: false }) {
      this.loading = true;
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
        this.loading = false;
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
    background-color: #f5f5f5;
    box-shadow: 0 5px 15px 0 rgba(0, 0, 0, 0.5);
    margin: 20px;
    height: 97%;
    overflow: hidden;
    position: relative;

    .title {
      padding: 15px;
      font-size: 16px;
      font-weight: 700;
      line-height: 20px;
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

      .ng-spinner {
        position: absolute;
        top: 50%;
        left: 50%;
        animation: spinning 1500ms linear infinite;
      }
    }
  }
}
</style>
