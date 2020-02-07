import Vue from 'vue';
import './scss/ngremotemedia.scss';
import 'cropperjs/dist/cropper.css';
import MediaModal from './components/MediaModal';
import UploadModal from './components/UploadModal';
import CropModal from './components/CropModal';
import { initDirective } from './utility/directives';
import vSelect from 'vue-select';
import { formatByteSize } from './utility/utility';
import './utility/polyfills';
import { truthy } from './utility/predicates';
import { objectFilter } from './utility/functional';

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
        selectedImage: {
          id: '',
          name: '',
          type: 'image',
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
        },
        allTags: []
      },
      computed: {
        nonImagePreviewClass() {
          return this.selectedImage.type === 'video' ? 'ng-video' : 'ng-book';
        },
        formattedSize() {
          return formatByteSize(this.selectedImage.size);
        },
        stringifiedVariations() {
          return JSON.stringify(
            objectFilter(truthy)(this.selectedImage.variations)
          );
        },
        isCroppable(){
          return !!this.selectedImage.id && this.selectedImage.type === "image" && Object.keys(this.config.availableVariations).length > 0;
        }
      },
      components: {
        'media-modal': MediaModal,
        'v-select': vSelect,
        'crop-modal': CropModal,
        'upload-modal': UploadModal,
      },
      methods: {

        async fetchFolders() {
          const response = await fetch(this.config.paths.folders);
          const folders = await response.json();
          this.folders = folders;
        },
        async handleBrowseMediaClicked() {
          this.mediaModalOpen = true;
          this.fetchFolders();
          if (document.querySelector('.ez-page-builder-wrapper')) {
            document.querySelector('.ez-page-builder-wrapper').style.transform = "none";
          }
        },
        handleCropClicked() {
          this.cropModalOpen = true;
        },
        handleMediaModalClose() {
          this.mediaModalOpen = false;
        },
        handleCropModalClose() {
          this.cropModalOpen = false;
        },
        handleUploadModalClose() {
          this.uploadModalOpen = false;
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
            size: item.filesize,
            variations: {},
            height: item.height,
            width: item.width
          };

          this.mediaModalOpen = false;
        },
        handleRemoveMediaClicked() {
          this.selectedImage = {
            id: '',
            name: '',
            type: 'image',
            url: '',
            alternateText: '',
            tags: [],
            size: 0,
            variations: {},
            height: 0,
            width: 0
          };
          this.$refs.fileInput.value = null;
        },
        handleFileInputChange(e) {
          this.uploadModalOpen = true;
          this.uploadModalLoading = true;

          this.fetchFolders();

          const file = e.target.files.item(0);

          if (file) {
            this.selectedImage = {
              id: file.name,
              name: file.name,
              type: this.getFileType(file),
              url: '',
              alternateText: '',
              tags: [],
              size: file.size,
              variations: {},
              height: 0,
              width: 0
            };

            if (this.selectedImage.type === "image"){
              const reader = new FileReader();
              reader.addEventListener(
                'load',
                function() {
                  this.$refs.image.onload = function() {
                    this.selectedImage.width = this.$refs.image.naturalWidth,
                    this.selectedImage.height = this.$refs.image.naturalHeight;
                    this.uploadModalLoading = false;
                  }.bind(this);

                  this.selectedImage.url = reader.result;
                }.bind(this),
                false
              );

              reader.readAsDataURL(file);
            } else {
              this.uploadModalLoading = false;
            }
          }
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
        getFileType(file){
          const type = file.type.split("/")[0];

          if (type !== "video" && type !== "image"){
            return "other";
          }

          return type;
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
