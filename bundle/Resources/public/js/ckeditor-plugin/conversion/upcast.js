/* eslint-disable no-console */
import { dataView, dataModel, attributes, datasetAttributes, defaultValue } from '../constants';
import { dataParamParse } from '../utils/data-param-conversion';

/**
 * Defines the upcast conversion.
 * Upcasting converts the saved view element into the model when loading data into the editor.
 */
const defineUpcast = (editor) => {
  editor.conversion.for('upcast').elementToElement({
    view: dataView,
    model(viewElement, { writer }) {
      return writer.createElement(dataModel.name, {
        [attributes.value]: dataParamParse(viewElement.getAttribute(`data-${datasetAttributes.value}`)),
        [attributes.fieldId]: viewElement.getAttribute(`data-${datasetAttributes.fieldId}`),
        [attributes.focusedField]: null,
      });
    },
  });
};

export default defineUpcast;
