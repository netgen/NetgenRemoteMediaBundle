import { dataModel, attributes, defaultValue } from '../constants';
import getFormat from '../utils/get-format';
import { createFieldId } from '../utils/field-id';
import loadLocation from './editing-downcast-utils/remote-resource-location/load';

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
        // Stored values are kept right away, so they are not lost when the editor data is saved
        // before the selected image is loaded, or when loading it fails
        [attributes.selectedImage]: {
          ...defaultValue.selectedImage,
          cssClass: cssClass || '',
          selectedVariation: variationName ? { label: variationName } : null,
        },
      });

      if (alignment) {
        writer.setAttribute(attributes.alignment, alignment, model);
      }

      if (locationId) {
        loadLocation({ editor, model, locationId });
      }

      return model;
    },
  });
};

export default defineUpcast;
