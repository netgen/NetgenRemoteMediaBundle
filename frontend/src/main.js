import Vue from 'vue';
import MediaModal from './components/MediaModal';
import './scss/ngremotemedia.scss';
import { initDirective } from './utility/directives';
import vSelect from 'vue-select';
import { formatByteSize } from './utility/utility';

Vue.config.productionTip = false;

var handleDOMContentLoaded = function() {
  document.querySelectorAll('.ngremotemedia-type').forEach((el, i) => {
    window[`remoteMedia${i}`] = new Vue({
      el,
      directives: {
        init: initDirective
      },
      data: {
        folders: [],
        modalOpen: false,
        selectedImage: {
          id: '',
          name: '',
          type: 'image',
          url: '',
          alternateText: '',
          tags: [],
          size: ''
        },
        allTags: []
      },
      computed: {
        nonImagePreviewClass() {
          return this.selectedImage.type === 'video' ? 'ng-video' : 'ng-book';
        },
        formattedSize() {
          return formatByteSize(this.selectedImage.size);
        }
      },
      components: {
        'media-modal': MediaModal,
        'v-select': vSelect
      },
      methods: {
        async handleBrowseMediaClicked() {
          this.modalOpen = true;
          const response = await fetch('/ngadminui/ngremotemedia/folders');
          const folders = await response.json();
          this.folders = folders;
        },
        handleModalClose() {
          this.modalOpen = false;
        },
        handleTagsInput(value) {
          this.allTags = [...new Set([...this.allTags, ...value])];
        },
        handleMediaSelected(item) {
          this.selectedImage = {
            id: item.resourceId,
            name: item.filename,
            type: item.type,
            url: item.url,
            alternateText: '',
            tags: item.tags,
            size: item.filesize
          };

          this.modalOpen = false;
        },
        handleRemoveMediaClicked() {
          this.selectedImage = {
            id: "",
            name: "",
            type: "image",
            url: "",
            alternateText: '',
            tags: [],
            size: 0
          };
        }
      },
      mounted() {
        this.allTags = [...this.selectedImage.tags];
      }
    });
  });
};

if (
  document.readyState === 'complete' ||
  (document.readyState !== 'loading' && !document.documentElement.doScroll)
) {
  handleDOMContentLoaded();
} else {
  document.addEventListener('DOMContentLoaded', handleDOMContentLoaded);
}
