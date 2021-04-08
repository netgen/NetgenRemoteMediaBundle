import Vue from 'vue';
import './scss/ngremotemedia.scss';
import 'cropperjs/dist/cropper.css';
import Interactions from './components/Interactions';
import EditorInsertModal from './components/EditorInsertModal';
import Modal from './components/Modal';
import { initDirective } from './utility/directives';
import './utility/polyfills';

/* eslint-disable prefer-arrow-callback */
(function (tinymce) {
  tinymce.PluginManager.add("ngremotemedia", function (editor) {
    const modalId = `${editor.editorId}_modal`;
    const modalNode = document.createElement("div");
    modalNode.setAttribute('id', modalId);
    document
      .getElementById(editor.editorId)
      .parentElement.appendChild(modalNode);
    window.ngrm = {
      ...window.ngrm,
    };
    window.ngrm[modalId] = {
      selectedImage: {},
      modal: new Vue({
        el: `#${modalId}`,
        directives: {
          init: initDirective,
        },
        data: {
          NgRemoteMediaTranslations,
          RemoteMediaSelectedImage: {},
          RemoteMediaConfig,
          editorInsertModalOpen: false,
          editorInsertModalLoading: false,
          selectedImage: {
            id: "",
            name: "",
            type: "image",
            mediaType: "image",
            url: "",
            browse_url: "",
            alternateText: "",
            tags: [],
            size: "",
            variations: {},
            height: 0,
            width: 0,
          },
          config: {
            paths: {},
            availableVariations: {},
          },
        },
        components: {
          "interactions": Interactions,
          "editor-insert-modal": EditorInsertModal,
          "modal": Modal,
        },
        methods: {
          openModal() {
            this.editorInsertModalOpen = true;
          },
          closeModal() {
            this.editorInsertModalOpen = false;
          },
        },
        template: `<editor-insert-modal
                        @close="closeModal"
                        v-show="editorInsertModalOpen"
                        :loading="editorInsertModalLoading"
                        :config="config"
                        :selected-image="selectedImage"
                        :translations="NgRemoteMediaTranslations"
                    ></editor-insert-modal>`,
      }),
    };

    // Add a button that opens a modal
    editor.addButton("ngremotemedia", {
      title: "Insert remote media",
      onclick() {
        window.ngrm[`${this.editorId}_modal`].modal.openModal();
      },
    });

    return {
      getMetadata() {
        return {
          name: "Netgen remote media",
          url: "https://github.com/netgen/NetgenRemoteMediaBundle",
        };
      },
    };
  });
})(tinymce);
