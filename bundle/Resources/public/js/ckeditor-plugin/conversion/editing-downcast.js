/* eslint-disable no-console */
import { dataView, dataModel, attributes } from '../constants';
import { toWidget } from '@ckeditor/ckeditor5-widget';
import renderField from './editing-downcast-utils/render-field';

/**
 * Defines the editing downcast conversion.
 * Editing downcasting converts the model element to the editable view element. Used when editing content.
 */
const defineEditingDowncast = (editor) => {
  editor.conversion.for('editingDowncast').elementToStructure({
    model: dataModel,
    view(modelElement, { writer }) {
      let domContentWrapper;

      const viewContentWrapper = writer.createRawElement(
        'div',
        {
          class: 'ngremotemedia-wrapper',
        },
        (domElement) => {
          domContentWrapper = domElement;
          renderField({ domElement, model: modelElement, editor });

          domContentWrapper.addEventListener('ngrm-change', ({ detail }) => {
            editor.model.change((writer) => {
              writer.setAttribute(attributes.value, detail.selectedImage, modelElement);
              writer.setAttribute(attributes.focusedField, detail.changeReason, modelElement);
            });
          });

          // Since there is a `data-cke-ignore-events` attribute set on the wrapper element in the editable mode,
          // the explicit `mousedown` handler on the `capture` phase is needed to move the selection onto the whole
          // widget
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
            true,
          );
        },
      );
      writer.setAttribute('data-cke-ignore-events', 'true', viewContentWrapper);

      const viewContainer = writer.createContainerElement(
        dataView.name,
        {
          class: dataView.classes,
          'data-label': 'Remote media file',
          dir: editor.locale.uiLanguageDirection,
        },
        viewContentWrapper,
      );

      return toWidget(viewContainer, writer, {
        label: 'Remote media file',
        hasSelectionHandle: true,
      });
    },
  });
};

export default defineEditingDowncast;
