import Vue from 'vue';
import PortalVue from 'portal-vue';
import vueDebounce from 'vue-debounce'
import './scss/ngremotemedia.scss';
import 'cropperjs/dist/cropper.css';
import Interactions from './components/Interactions';
import { initDirective } from './utility/directives';
import './utility/polyfills';
import SelectFolderInteraction from "./components/SelectFolderInteraction.vue";

Vue.config.productionTip = false;
Vue.use(PortalVue);
Vue.use(vueDebounce);

const initInteractionsVue = (el) => {
    window[`ngrm_interactions_vue_${el.dataset.id}`] = new Vue({
        el,
        directives: {
          init: initDirective
        },
        data: {
            config: {
                mode: 'field', // field | embed
                paths: {
                    browse_resources: '/resource/browse',
                    upload_resources: '/resource/upload',
                    load_facets: '/facets/load',
                    load_folders: '/folder/load',
                    create_folder: '/folder/create',
                },
                translations: {},
                inputFields: {
                    'locationId': 'locationId',
                    'remoteId': 'remoteId',
                    'type': 'type',
                    'altText': 'altText',
                    'caption': 'caption',
                    'tags': 'tags[]',
                    'cropSettings': 'cropSettings',
                    'source': 'source',
                    'watermarkText': 'watermarkText',
                    'cssClass': 'cssClass',
                    'selectedVariation': 'selectedVariation',
                },
                availableVariations: [],
                allowedVisibilities: [],
                allowedTypes: [],
                allowedTags: [],
                parentFolder: null,
                folder: null,
                uploadContext: {},
                disableUpload: false,
                hideFilename: false,
            },
            selectedImage: {
                id: '',
                name: '',
                type: 'image',
                format: '',
                url: '',
                browse_url: '',
                previewUrl: '',
                alternateText: '',
                caption: '',
                watermarkText: '',
                tags: [],
                size: '',
                variations: {},
                height: 0,
                width: 0,
                selectedVariation: null,
                cssClass: '',        
            },
        },
        components: {
            'interactions': Interactions
        }
    });
};

const initSelectFolderVue = (el) => {
    window[`ngrm_select_folder_vue_${el.dataset.id}`] = new Vue({
        el,
        directives: {
            init: initDirective
        },
        data: {
            config: {
                paths: {
                    load_folders: '/folder/load',
                    create_folder: '/folder/create',
                },
                translations: {},
                inputFields: {
                    'folder': 'folder',
                },
            },
            selectedFolder: null
        },
        components: {
            'select-folder-interaction': SelectFolderInteraction
        }
    });
};

const observerCallback = () => {
    const interactionsContainers = document.getElementsByClassName('ngremotemedia-container');

    for(let i=0; i < interactionsContainers.length; i++) {
        const container = interactionsContainers[i];

        if (container.querySelector('interactions') === null) {
            continue;
        }

        initInteractionsVue(container);
    }

    const selectFolderContainers = document.getElementsByClassName('ngremotemedia-select-folder-container');

    for(let i=0; i < selectFolderContainers.length; i++) {
        const container = selectFolderContainers[i];

        if (container.querySelector('select-folder-interaction') === null) {
            continue;
        }

        initSelectFolderVue(container);
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
