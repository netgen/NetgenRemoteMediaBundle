<template>
  <modal v-bind:title="this.$root.$data.NgRemoteMediaTranslations.browse_title" @close="$emit('close')">
    <media-facets :tags="tags" :types="types" :facets="facets" :facets-loading="facetsLoading" @change="handleFacetsChange" />
    <media-galery
        :media="media"
        :canLoadMore="canLoadMore"
        :selectedMediaId="selectedMediaId"
        :loading="loading"
        @loadMore="handleLoadMore"
        @media-selected="item => $emit('media-selected', item)"
    />
    <i v-if="loading" class="ng-icon ng-spinner" />
  </modal>
</template>

<script>
import MediaFacets from "./MediaFacets";
import MediaGallery from "./MediaGalery";
import { encodeQueryData } from "../utility/utility";
import debounce from "debounce";
import Modal from "./Modal";
import { FOLDER_ROOT } from "../constants/facets";

const NUMBER_OF_ITEMS = 25;

export default {
  name: "MediaModal",
  props: ["tags", "types", "facetsLoading", "selectedMediaId", "paths"],
  components: {
    "media-facets": MediaFacets,
    "media-galery": MediaGallery,
    modal: Modal
  },
  data() {
    return {
      media: [],
      canLoadMore: false,
      nextCursor: null,
      loading: true,
      facets: {
        folder: "",
        type: "",
        query: "",
        tag: ""
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
      };

      if (this.facets.query) {
        query['query'] = this.facets.query;
      }

      if (this.facets.type) {
        query['type'] = this.facets.type;
      }

      if (this.facets.folder) {
        query['folder'] = this.facets.folder === FOLDER_ROOT
          ? ''
          : this.facets.folder;
      }

      if (this.facets.tag) {
        query['tag'] = this.facets.tag;
      }

      if (patch && this.nextCursor) {
        query['next_cursor'] = this.nextCursor;
      }

      const url = `${this.paths.browse_resources}?${encodeQueryData(query)}`;

      try {
        const response = await fetch(url, {
          signal: this.abortController.signal
        });
        const media = await response.json();

        this.media = patch ? this.media.concat(media.hits) : media.hits;
        this.canLoadMore = media.load_more;
        this.nextCursor = media.next_cursor;
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
.ng-spinner {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);

  &:before {
    display: inline-block;
    animation: spinning 1500ms linear infinite;
  }
}
</style>
