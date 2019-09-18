import Vue from 'vue';
import MediaModal from './components/MediaModal';
import './scss/ngremotemedia.scss';

Vue.config.productionTip = false;

document.querySelectorAll('.ngremotemedia-type').forEach((el, i) => {
  window[`remoteMedia${i}`] = new Vue({
    el,
    data: {
      folders: [],
      modalOpen: false
    },
    components: {
      'media-modal': MediaModal
    },
    methods: {
      async browseMedia() {
        const response = await fetch('ngadminui/ngremotemedia/folders');
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
