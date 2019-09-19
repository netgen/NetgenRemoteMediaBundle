import Vue from 'vue';
import MediaModal from './components/MediaModal';
import './scss/ngremotemedia.scss';
import { initDirective } from './utility/directives';

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
        selectedImage: {}
      },
      computed: {
        nonImagePreviewClass() {
          return this.selectedImage.type === 'video' ? 'ng-video' : 'ng-book';
        }
      },
      components: {
        'media-modal': MediaModal
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
