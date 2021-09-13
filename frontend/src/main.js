import Vue from 'vue';
import './scss/ngremotemedia.scss';
import 'cropperjs/dist/cropper.css';
import Interactions from './components/Interactions';
import EditorInsertModal from './components/EditorInsertModal';
import { initDirective } from './utility/directives';
import './utility/polyfills';

Vue.config.productionTip = false;

const handleDOMContentLoaded = function() {
    document.querySelectorAll('.ngremotemedia-container').forEach((el, i) => {
        window[`remoteMedia${el.dataset.id}`] = new Vue({
            el,
            directives: {
                init: initDirective
            },
            data: {
                NgRemoteMediaTranslations,
                NgRemoteMediaSelectedImage : window[`NgRemoteMediaSelectedImage_${el.dataset.id}`],
                NgRemoteMediaConfig,
                NgRemoteMediaInputFields : window[`NgRemoteMediaInputFields_${el.dataset.id}`],
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
                    mediaType: 'image',
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
                        mediaType: 'image',
                        format: '',
                        url: '',
                        previewUrl: '',
                        browse_url: '',
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

                    if (typeof data.resourceType !== 'undefined' && data.resourceType !== null && typeof data.resourceId !== 'undefined' && data.resourceId !== null) {
                        const response = await fetch(this.config.paths.editor_fetch + '?resource_type=' + data.resourceType + '&resource_id=' + data.resourceId);
                        const item = await response.json();

                        this.selectedImage = {
                            id: item.resourceId,
                            name: item.filename,
                            type: item.type,
                            mediaType: item.mediaType,
                            format: item.format,
                            url: item.url,
                            previewUrl: item.preview_url,
                            alternateText: item.alt_text,
                            tags: item.tags,
                            size: item.filesize,
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
