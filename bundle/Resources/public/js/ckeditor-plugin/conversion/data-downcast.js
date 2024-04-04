/* eslint-disable no-console, no-param-reassign */
import { dataView, dataModel, attributes, pluginKey } from '../constants';
import { dataParamStringify } from '../utils/data-param-conversion';

/**
 * Defines the data downcast conversion.
 * Data downcasting converts the model element to the read-only view element for saving in the database.
 * This element is also rendered on the front web.
 */
const defineDataDowncast = (editor) => {
  editor.conversion.for('dataDowncast').elementToElement({
    model: dataModel.name,
    view(modelElement, { writer }) {
      const selectedImage = modelElement.getAttribute(attributes.selectedImage);
      return writer.createContainerElement(
        dataView.name,
        {
          class: dataView.classes,
          [`data-${attributes.selectedImage}`]: dataParamStringify(modelElement.getAttribute(attributes.selectedImage)),
          [`data-${attributes.fieldId}`]: modelElement.getAttribute(attributes.fieldId),
          [`data-${attributes.locationId}`]: modelElement.getAttribute(attributes.locationId),
        },
        [
          writer.createEmptyElement(
            'img',
            {
              src: selectedImage.url,
              width: selectedImage.width,
              height: selectedImage.height,
            },
          ),
        ],
      );
    },
  });
};

export default defineDataDowncast;
