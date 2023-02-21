import Vue from 'vue';
import './scss/ngremotemedia.scss';
import 'cropperjs/dist/cropper.css';
import Interactions from './components/Interactions';
import EditorInsertModal from './components/EditorInsertModal';
import { initDirective } from './utility/directives';
import './utility/polyfills';

Vue.config.productionTip = false;

const initVue = (el) => {
    window[`ngrm_app_${el.dataset.id}`] = new Vue({
        el,
        directives: {
          init: initDirective
        },
        data: {
            NgRemoteMediaTranslations,
            NgRemoteMediaSelectedImage : window[`NgRemoteMediaSelectedImage_${el.dataset.id}`],
            NgRemoteMediaConfig,
            NgRemoteMediaInputFields : window[`NgRemoteMediaInputFields_${el.dataset.id}`],
            NgRemoteMediaOptions : window[`NgRemoteMediaOptions_${el.dataset.id}`],
            NgRemoteMediaAvailableVariations : window[`NgRemoteMediaAvailableVariations_${el.dataset.id}`],
            NgRemoteMediaAvailableEditorVariations : window[`NgRemoteMediaAvailableEditorVariations_${el.dataset.id}`],
            editorInsertModalOpen: false,
            editorInsertModalLoading: false,
            editorInsertCallback: null,
            selectedEditorVariation: '',
            caption: '',
            cssClass: '',
            selectedImage: {
                id: '',
                name: '',
                type: 'image',
                format: '',
                url: '',
                browse_url: '',
                previewUrl: '',
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
            setEditorInsertCallback(callback) {
                this.editorInsertCallback = callback;
            },
            handleEditorInsertModalClose() {
                this.editorInsertModalLoading = false;
                this.editorInsertModalOpen = false;
            },
            async openEditorInsertModal(data) {
                data = data || {};

                this.editorInsertModalLoading = true;
                this.editorInsertModalOpen = true;

                this.selectedImage = {
                    id: '',
                    name: '',
                    type: 'image',
                    format: '',
                    url: '',
                    previewUrl: '',
                    browseUrl: '',
                    alternateText: '',
                    tags: [],
                    size: '',
                    variations: {},
                    height: 0,
                    width: 0
                };

                this.caption = '';
                this.cssClass = '';
                this.selectedEditorVariation = '';

                var variations = {};

                if (typeof data.image_variations !== "undefined") {
                    variations = data.image_variations;
                }

                if (typeof data.type !== 'undefined' && data.type !== null && typeof data.remoteId !== 'undefined' && data.remoteId !== null) {
                    const response = await fetch(this.config.paths.editor_fetch + '?resource_type=' + data.remoteId + '&resource_id=' + data.remoteId);
                    const item = await response.json();

                    this.selectedImage = {
                        id: item.remoteId,
                        name: item.filename,
                        type: item.type,
                        format: item.format,
                        url: item.url,
                        previewUrl: item.previewUrl,
                        alternateText: item.altText,
                        tags: item.tags,
                        size: item.size,
                        variations: variations,
                        height: item.height,
                        width: item.width
                    };
                }

                if (typeof data.caption !== 'undefined') {
                    this.caption = data.caption;
                }

                if (typeof data.cssclass !== 'undefined') {
                    this.cssClass = data.cssclass;
                }

                if (typeof data.variation !== 'undefined') {
                    this.selectedEditorVariation = data.variation;
                }

                this.editorInsertModalLoading = false;
            }
        }
    });
};


const observerCallback = () => {
    var containers = document.getElementsByClassName('ngremotemedia-container');

    for(var i=0; i < containers.length; i++) {
        let el = containers[i];

        if (typeof window[`ngrm_app_${el.dataset.id}`] !== 'undefined') {
            continue;
        }

        initVue(el);
    }
};

const targetNode = document.body ? document.body : document;
const config = { attributes: true, childList: true, subtree: true };
const observer = new MutationObserver(observerCallback);

if (
  document.readyState === 'complete' ||
  (document.readyState !== 'loading' && !document.documentElement.doScroll)
) {
  observer.observe(targetNode, config);

} else {
  observer.observe(targetNode, config);
}
