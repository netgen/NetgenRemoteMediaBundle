import Vue from 'vue';
import './scss/ngremotemedia.scss';
import 'cropperjs/dist/cropper.css';
import Interactions from './components/Interactions';
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
            config: {
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
                },
                availableVariations: [],
                allowedVisibilities: [],
                allowedTypes: [],
                allowedTags: [],
                parentFolder: null,
                folder: null,
                uploadContext: {},
                disableUpload: false,
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
                tags: [],
                size: '',
                variations: {},
                height: 0,
                width: 0
            },
        },
        components: {
            'interactions': Interactions
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
