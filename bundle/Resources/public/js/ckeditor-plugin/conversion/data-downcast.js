/* eslint-disable no-console, no-param-reassign */
import { dataView, dataModel, attributes, viewAttributes, pluginKey } from '../constants';

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
      const locationId = modelElement.getAttribute(attributes.locationId);
      const wrapper = writer.createContainerElement(
        dataView.name,
        {
          class: dataView.classes,
          style: `alignment=${modelElement.getAttribute(attributes.alignment)};`,
          [viewAttributes.fieldId]: modelElement.getAttribute(attributes.fieldId),
          [viewAttributes.locationId]: locationId,
          [viewAttributes.cssClass]: selectedImage.cssClass,
          [viewAttributes.variationName]: selectedImage.selectedVariation?.label ?? null,
          [viewAttributes.variationGroup]: editor.config.get(pluginKey).variationGroup,
          [viewAttributes.viewEndpoint]: editor.config.get(pluginKey).endpoints.viewResource(locationId),
          [viewAttributes.alignment]: modelElement.getAttribute(attributes.alignment),
        },
      );

      return wrapper;
    },
  });
};

export default defineDataDowncast;
