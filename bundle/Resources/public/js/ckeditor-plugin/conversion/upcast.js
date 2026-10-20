import { dataModel, attributes, pluginKey, defaultValue } from '../constants';
import getFormat from '../utils/get-format';
import { createFieldId } from '../utils/field-id';

/**
 * Defines the upcast conversion.
 * Upcasting converts the saved view element (data downcast) into the model when loading the editor.
 * The model is then used in editing downcast to render the editor field.
 */
const defineUpcast = (editor) => {
  const format = getFormat(editor);

  editor.conversion.for('upcast').elementToElement({
    view: format.matcher,
    converterPriority: format.priority ?? 'normal',
    model(viewElement, { writer }) {
      const { fieldId, locationId, cssClass, variationName, alignment } = format.fromView(viewElement);
      const model = writer.createElement(dataModel.name, {
        [attributes.fieldId]: fieldId || createFieldId(editor),
        [attributes.locationId]: locationId || null,
        [attributes.selectedImage]: defaultValue.selectedImage,
      });

      if (alignment) {
        writer.setAttribute(attributes.alignment, alignment, model);
      }

      const getSelectedImageEndpoint = editor.config.get(pluginKey).endpoints.getSelectedImage ?? defaultValue.endpoints.getSelectedImage;

      fetch(getSelectedImageEndpoint(locationId))
        .then(response => response.json())
        .then((selectedImage) => {
          editor.model.change((writer) => {
            writer.setAttribute(
              attributes.selectedImage,
              {
                ...selectedImage,
                cssClass,
                selectedVariation: {
                  label: variationName,
                  value: selectedImage.variations?.[variationName],
                },
              },
              model,
            );
          });
        });

      return model;
    },
  });
};

export default defineUpcast;
