/* eslint-disable no-console */
import { dataView, dataModel, attributes, viewAttributes, pluginKey, defaultValue } from '../constants';

/**
 * Defines the upcast conversion.
 * Upcasting converts the saved view element (data downcast) into the model when loading the editor.
 * The model is then used in editing downcast to render the editor field.
 */
const defineUpcast = (editor) => {
  editor.conversion.for('upcast').elementToElement({
    view: dataView,
    model(viewElement, { writer }) {
      const locationId = viewElement.getAttribute(viewAttributes.locationId);
      const model = writer.createElement(dataModel.name, {
        [attributes.fieldId]: viewElement.getAttribute(viewAttributes.fieldId),
        [attributes.locationId]: locationId,
        [attributes.selectedImage]: defaultValue.selectedImage,
      });

      fetch(editor.config.get(pluginKey).endpoints.getSelectedImage(locationId))
        .then(response => response.json())
        .then((selectedImage) => {
          editor.model.change((writer) => {
            const selectedVariationName = viewElement.getAttribute(viewAttributes.variationName);
            writer.setAttribute(
              attributes.selectedImage,
              {
                ...selectedImage,
                cssClass: viewElement.getAttribute(viewAttributes.cssClass),
                selectedVariation: {
                  label: selectedVariationName,
                  value: selectedImage.variations[selectedVariationName]},
              },
              model,
            );
          })
        });

      return model;
    },
  });
};

export default defineUpcast;
