/* eslint-disable no-console */
import { dataView, dataModel, attributes, datasetAttributes } from '../constants';

/**
 * Defines the upcast conversion.
 * Upcasting converts the saved view element into the model when loading data into the editor.
 */
const defineUpcast = (editor) => {
  editor.conversion.for('upcast').elementToElement({
    view: dataView,
    // TODO: figure this out, mine won't be raw content
    // The div.raw-html-embed is registered as a raw content element,
    // so all it's content is available in a custom property.
    model(viewElement, { writer }) {
      return writer.createElement(dataModel.name, {
        [attributes.value]: JSON.parse(viewElement.getAttribute(`data-${datasetAttributes.value}`)),
        [attributes.fieldId]: viewElement.getAttribute(`data-${datasetAttributes.fieldId}`),
        [attributes.focusedField]: null,
      });
    },
  });
};

export default defineUpcast;
