import { dataModel, attributes, pluginKey } from '../constants';
import getFormat from '../utils/get-format';

/**
 * Defines the data downcast conversion.
 * Data downcasting converts the model element to the read-only view element for saving in the database.
 * The stored markup is defined by the configured format, see `formats/html.js`.
 */
const defineDataDowncast = (editor) => {
  const format = getFormat(editor);

  editor.conversion.for('dataDowncast').elementToElement({
    model: dataModel.name,
    view(modelElement, { writer }) {
      const selectedImage = modelElement.getAttribute(attributes.selectedImage);

      return format.toView(
        writer,
        {
          fieldId: modelElement.getAttribute(attributes.fieldId),
          locationId: modelElement.getAttribute(attributes.locationId),
          cssClass: selectedImage.cssClass,
          variationName: selectedImage.selectedVariation?.label ?? null,
          alignment: modelElement.getAttribute(attributes.alignment) ?? null,
        },
        editor.config.get(pluginKey),
      );
    },
  });
};

export default defineDataDowncast;
