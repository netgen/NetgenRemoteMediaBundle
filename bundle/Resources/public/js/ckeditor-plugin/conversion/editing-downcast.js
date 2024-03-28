/* eslint-disable no-console */
import { dataView, dataModel } from '../constants';
import { toWidget } from '@ckeditor/ckeditor5-widget';
import renderField from './editing-downcast-utils/render-field';

/**
 * Defines the editing downcast conversion.
 * Editing downcasting converts the model element to the editable view element. Used when editing content.
 */
const defineEditingDowncast = (editor) => {
  //   const editorView = editor.editing.view;
  editor.conversion.for('editingDowncast').elementToStructure({
    model: dataModel,
    view(modelElement, { writer }) {
      let domContentWrapper;
      let state;
      let props;

      const viewContentWrapper = writer.createRawElement(
        'div',
        {
          class: 'ngremotemedia-wrapper',
        },
        (domElement) => {
          domContentWrapper = domElement;

          renderField(domElement);

          // Since there is a `data-cke-ignore-events` attribute set on the wrapper element in the editable mode,
          // the explicit `mousedown` handler on the `capture` phase is needed to move the selection onto the whole
          // HTML embed widget.
          domContentWrapper.addEventListener(
            'mousedown',
            () => {
              const { model } = editor;
              const selectedElement = model.document.selection.getSelectedElement();

              // Move the selection onto the whole HTML embed widget if it's currently not selected.
              if (selectedElement !== modelElement) {
                model.change((writer) => writer.setSelection(modelElement, 'on'));
              }
            },
            true
          );
        }
      );
      writer.setAttribute('data-cke-ignore-events', 'true', viewContentWrapper);

      // API exposed on each raw HTML embed widget so other features can control a particular widget.
      //   const rawHtmlApi = {
      //     makeEditable() {
      //       state = Object.assign({}, state, {
      //         isEditable: true,
      //       });

      //       renderContent({ domElement: domContentWrapper, editor, state, props });

      //       editorView.change((writer) => {
      //         writer.setAttribute('data-cke-ignore-events', 'true', viewContentWrapper);
      //       });

      //       // This could be potentially pulled to a separate method called focusTextarea().
      //       domContentWrapper.querySelector('textarea').focus();
      //     },
      //     save(newValue) {
      //       // If the value didn't change, we just cancel. If it changed,
      //       // it's enough to update the model – the entire widget will be reconverted.
      //       if (newValue !== state.getRawHtmlValue()) {
      //         editor.execute('htmlEmbed', newValue);
      //         editor.editing.view.focus();
      //       } else {
      //         this.cancel();
      //       }
      //     },
      //     cancel() {
      //       state = Object.assign({}, state, {
      //         isEditable: false,
      //       });

      //       renderContent({ domElement: domContentWrapper, editor, state, props });
      //       editor.editing.view.focus();

      //       editorView.change((writer) => {
      //         writer.removeAttribute('data-cke-ignore-events', viewContentWrapper);
      //       });
      //     },
      //   };

      //   state = {
      //     showPreviews: htmlEmbedConfig.showPreviews,
      //     isEditable: false,
      //     getRawHtmlValue: () => modelElement.getAttribute('value') || '',
      //   };

      //   props = {
      //     sanitizeHtml: htmlEmbedConfig.sanitizeHtml,
      //     textareaPlaceholder: t('Paste raw HTML here...'),

      //     onEditClick() {
      //       rawHtmlApi.makeEditable();
      //     },
      //     onSaveClick(newValue) {
      //       rawHtmlApi.save(newValue);
      //     },
      //     onCancelClick() {
      //       rawHtmlApi.cancel();
      //     },
      //   };

      const viewContainer = writer.createContainerElement(
        dataView.name,
        {
          class: dataView.classes,
          'data-label': 'Remote media file',
          dir: editor.locale.uiLanguageDirection,
        },
        viewContentWrapper
      );

      writer.setCustomProperty('data-ngrm-test', { test: 'a' }, viewContainer);
      //   writer.setCustomProperty('rawHtml', true, viewContainer);

      return toWidget(viewContainer, writer, {
        label: 'Remote media file',
        hasSelectionHandle: true,
      });
    },
  });
};

export default defineEditingDowncast;
