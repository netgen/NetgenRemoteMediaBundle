/* eslint-disable no-console */
import { dataView, dataModel, attributes, datasetAttributes, defaultValue, editingView } from '../constants';
import { dataParamParse } from '../utils/data-param-conversion';

/**
 * Defines the upcast conversion.
 * Upcasting converts the saved view element (data downcast) into the model when loading the editor.
 * The model is then used in editing downcast to render the editor field.
 */
const defineUpcast = (editor) => {
  editor.conversion.for('upcast').elementToElement({
    view: dataView,
    model(viewElement, { writer }) {
      return writer.createElement(dataModel.name, {
        [attributes.selectedImage]: dataParamParse(viewElement.getAttribute(`data-${attributes.selectedImage}`)),
        [attributes.fieldId]: viewElement.getAttribute(`data-${attributes.fieldId}`),
        [attributes.locationId]: viewElement.getAttribute(`data-${attributes.locationId}`),
      });
    },
  });
};

export default defineUpcast;
