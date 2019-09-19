import Vue from 'vue';
import MediaModal from './components/MediaModal';
import './scss/ngremotemedia.scss';
import { initDirective } from './utility/directives';
import vSelect from 'vue-select';

Vue.config.productionTip = false;

var handleDOMContentLoaded = function() {
  document.querySelectorAll('.ngremotemedia-type').forEach((el, i) => {
    window[`remoteMedia${i}`] = new Vue({
      el,
      directives: {
        init: initDirective
      },
      data: {
        RemoteMediaSelectedImage,
        folders: [],
        modalOpen: false,
        selectedImage: {},
        allTags: []
      },
      computed: {
        nonImagePreviewClass() {
          return this.selectedImage.type === 'video' ? 'ng-video' : 'ng-book';
        },
        selectedTags: {
          get() {
            return this.selectedImage && this.selectedImage.tags
              ? this.selectedImage.tags.join(',')
              : [];
          },
          set(val) {
            this.selectedImage.tags = val.split(',');
          }
        }
      },
      components: {
        'media-modal': MediaModal,
        'v-select': vSelect
      },
      methods: {
        async browseMedia() {
          const response = await fetch('/ngadminui/ngremotemedia/folders');
          const folders = await response.json();
          this.folders = folders;
          this.modalOpen = true;
        },
        handleModalClose() {
          this.modalOpen = false;
        }
      },
      mounted() {
        this.allTags = this.selectedImage.tags;
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
