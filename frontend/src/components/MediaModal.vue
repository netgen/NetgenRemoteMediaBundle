<template>
  <modal v-bind:title="this.$root.$data.NgRemoteMediaTranslations.browse_title" @close="$emit('close')">
    <media-facets :tags="tags" :media-types="mediaTypes" :facets="facets" :facets-loading="facetsLoading" @change="handleFacetsChange" />
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
import MediaGalery from "./MediaGalery";
import {FOLDER_ALL, FOLDER_ROOT, TAG_ALL, SEARCH_NAME, TYPE_ALL, TYPE_IMAGE, TYPE_VIDEO, TYPE_RAW} from "../constants/facets";
import { encodeQueryData } from "../utility/utility";
import debounce from "debounce";
import Modal from "./Modal";

const NUMBER_OF_ITEMS = 25;

export default {
  name: "MediaModal",
  props: ["tags", "facetsLoading", "selectedMediaId", "paths"],
  components: {
    "media-facets": MediaFacets,
    "media-galery": MediaGalery,
    modal: Modal
  },
  data() {
    let folder = FOLDER_ALL;
    if (this.$root.$data.NgRemoteMediaFolderConfig.parentFolder) {
      folder = this.$root.$data.NgRemoteMediaFolderConfig.parentFolder.id;
    }

    if (this.$root.$data.NgRemoteMediaFolderConfig.folder) {
      folder = this.$root.$data.NgRemoteMediaFolderConfig.folder.id;
    }

    return {
      media: [],
      canLoadMore: false,
      nextCursor: null,
      loading: true,
      facets: {
        folder: folder,
        searchType: SEARCH_NAME,
        mediaType: "",
        query: "",
        tag: ""
      },
      mediaTypes: [
        {
          name: this.$root.$data.NgRemoteMediaTranslations.browse_image_and_documents,
          id: TYPE_IMAGE
        },
        {
          name: this.$root.$data.NgRemoteMediaTranslations.browse_video_and_audio,
          id: TYPE_VIDEO
        },
        {
          name: this.$root.$data.NgRemoteMediaTranslations.browse_raw,
          id: TYPE_RAW
        }
      ]
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
        mediatype: this.facets.mediaType || TYPE_ALL,
        folder: this.facets.folder || FOLDER_ALL,
        search_type: this.facets.searchType,
        next_cursor: patch ? this.nextCursor : null,
        tag: this.facets.tag || TAG_ALL
      };

      const url = `${this.paths.browse}?${encodeQueryData(query)}`;

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
