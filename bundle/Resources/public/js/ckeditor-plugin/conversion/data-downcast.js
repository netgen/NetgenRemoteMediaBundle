/* eslint-disable no-console, no-param-reassign */
import { dataView, dataModel, attributes } from '../constants';
import { dataParamStringify } from '../utils/data-param-conversion';
import htmlToView from './data-downcast-utils/html-to-view';

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
      const wrapper = writer.createContainerElement(
        dataView.name,
        {
          class: dataView.classes,
          [`data-${attributes.selectedImage}`]: dataParamStringify(modelElement.getAttribute(attributes.selectedImage)),
          [`data-${attributes.fieldId}`]: modelElement.getAttribute(attributes.fieldId),
          [`data-${attributes.locationId}`]: modelElement.getAttribute(attributes.locationId),
        },
      );

      if (selectedImage.id) {
        wrapper._appendChild(htmlToView(`
        <div class="ngremotemedia-image">
          <img src="${selectedImage.url}" width="${selectedImage.width}" height="${selectedImage.height}">
        </div>`, writer));
      }

      return wrapper;
    },
  });
};

export default defineDataDowncast;
