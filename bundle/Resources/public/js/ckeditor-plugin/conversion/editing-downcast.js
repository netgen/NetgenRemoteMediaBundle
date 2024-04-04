/* eslint-disable no-console */
import { dataModel, attributes, pluginKey, editingView } from '../constants';
import { toWidget } from '@ckeditor/ckeditor5-widget';
import renderField from './editing-downcast-utils/render-field';
import handleAlignment from './editing-downcast-utils/operation-handlers/alignment';

/**
 * Defines the editing downcast conversion.
 * Editing downcasting converts the model element to the editable view element. Used when editing content.
 */
const defineEditingDowncast = (editor) => {
  editor.model.document.on('change:data', (event, action) => {
    const selectedElement = event.source.selection.getSelectedElement();
    if (selectedElement?.name !== pluginKey) {
      return;
    }

    if (action.operations[0]?.key === 'alignment') {
      handleAlignment({ selectedElement, editor, action });

      return;
    }
  });

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
              const oldImage = modelElement.getAttribute(attributes.selectedImage);
              writer.setAttribute(attributes.selectedImage, detail.selectedImage, modelElement);
              if (detail.selectedImage.id !== oldImage.id) {
                renderField({ domElement, model: modelElement, editor });
              }
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
        editingView.name,
        {
          class: editingView.classes,
          'data-label': 'Remote media file',
          'field-id': modelElement.getAttribute(attributes.fieldId),
          dir: editor.locale.uiLanguageDirection,
        },
        viewContentWrapper,
      );

      const view = toWidget(viewContainer, writer, {
        label: 'Remote media file',
        hasSelectionHandle: true,
      });

      return view;
    },
  });
};

export default defineEditingDowncast;
