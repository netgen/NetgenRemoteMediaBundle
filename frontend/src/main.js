import Vue from 'vue';
import './scss/ngremotemedia.scss';
import 'cropperjs/dist/cropper.css';
import Interactions from './components/Interactions';
import MediaModal from './components/MediaModal';
import UploadModal from './components/UploadModal';
import CropModal from './components/CropModal';
import EditorInsertModal from './components/EditorInsertModal';
import { initDirective } from './utility/directives';
import vSelect from 'vue-select';
import './utility/polyfills';
import { truthy } from './utility/predicates';
import { objectFilter } from './utility/functional';
import {formatByteSize} from "./utility/utility";

Vue.config.productionTip = false;

const handleDOMContentLoaded = function() {
  document.querySelectorAll('.ngremotemedia-type').forEach((el, i) => {
    window[`remoteMedia${i}`] = new Vue({
      el,
      directives: {
        init: initDirective
      },
      data: {
        NgRemoteMediaTranslations,
        RemoteMediaSelectedImage : window[`RemoteMediaSelectedImage_${el.dataset.id}`],
        RemoteMediaConfig,
        folders: [],
        mediaModalOpen: false,
        cropModalOpen: false,
        uploadModalOpen: false,
        uploadModalLoading: false,
        editorInsertModalOpen: false,
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
      computed: {
        stringifiedVariations() {
          return JSON.stringify(
            objectFilter(truthy)(this.selectedImage.variations)
          );
        },
      },
      components: {
        'interactions': Interactions,
        'media-modal': MediaModal,
        'v-select': vSelect,
        'crop-modal': CropModal,
        'upload-modal': UploadModal,
        'editor-insert-modal': EditorInsertModal
      },
      methods: {
        prepareDomForModal() {
          const query = document.querySelector('.ez-page-builder-wrapper')
          if (query) {
            query.style.transform = "none";
          }
        },
        resetDomAfterModal() {
          const query = document.querySelector('.ez-page-builder-wrapper')
          if (query) {
            query.removeAttribute("style");
          }
        },
        handleEditorInsertClicked() {
          this.editorInsertModalOpen = true;
        },
        handleMediaModalClose() {
          this.mediaModalOpen = false;
          this.resetDomAfterModal();
        },
        handleCropModalClose() {
          this.cropModalOpen = false;
          this.resetDomAfterModal();
        },
        handleUploadModalClose() {
          this.uploadModalOpen = false;
        },
        handleEditorInsertModalClose() {
          this.editorInsertModalOpen = false;
        },
        handleMediaSelected(item) {
          this.selectedImage = {
            id: item.resourceId,
            name: item.filename,
            type: item.type,
            mediaType: item.mediaType,
            url: item.url,
            alternateText: '',
            tags: item.tags,
            size: item.filesize,
            variations: {},
            height: item.height,
            width: item.width
          };

          this.mediaModalOpen = false;
        },
        handleVariationCropChange(newValues) {
          this.selectedImage = {
            ...this.selectedImage,
            variations: {
              ...this.selectedImage.variations,
              ...newValues
            }
          };
        },
        handleUploadModalSave(name){
          this.selectedImage = {
            ...this.selectedImage,
            name,
            id: name
          };
          this.uploadModalOpen = false;
        },
        handleEditorInsertModalSave(){
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
