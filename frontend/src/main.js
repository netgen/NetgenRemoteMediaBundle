import Vue from 'vue';
import './scss/ngremotemedia.scss';
import 'cropperjs/dist/cropper.css';
import Interactions from './components/Interactions';
import EditorInsertModal from './components/EditorInsertModal';
import { initDirective } from './utility/directives';
import './utility/polyfills';

Vue.config.productionTip = false;

const handleDOMContentLoaded = function() {
  document.querySelectorAll('.ngremotemedia-type').forEach((el, i) => {
    window[`remoteMedia${el.dataset.id}`] = new Vue({
      el,
      directives: {
        init: initDirective
      },
      data: {
        NgRemoteMediaTranslations,
        RemoteMediaSelectedImage : window[`RemoteMediaSelectedImage_${el.dataset.id}`],
        RemoteMediaConfig,
        editorInsertModalOpen: false,
        editorInsertModalLoading: false,
        selectedImage: {
          id: '',
          name: '',
          type: 'image',
          mediaType: 'image',
          url: '',
          browse_url: '',
          alternateText: '',
          tags: [],
          size: '',
          variations: {},
          height: 0,
          width: 0
        },
        config: {
          paths: {},
          availableVariations: {}
        }
      },
      components: {
        'interactions': Interactions,
        'editor-insert-modal': EditorInsertModal
      },
      methods: {
        handleEditorInsertClicked() {
          this.editorInsertModalOpen = true;
        },
        handleEditorInsertModalClose() {
            this.editorInsertModalOpen = false;
        },
        handleEditorInsertModalSave(){
          this.editorInsertModalLoading = false;
          this.editorInsertModalOpen = false;
        }
      },
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
