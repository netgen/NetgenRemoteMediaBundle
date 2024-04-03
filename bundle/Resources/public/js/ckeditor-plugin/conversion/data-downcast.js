/* eslint-disable no-console, no-param-reassign */
import { dataView, dataModel, attributes, datasetAttributes } from '../constants';
import { dataParamStringify } from '../utils/data-param-conversion';

/**
 * Defines the data downcast conversion.
 * Data downcasting converts the model element to the read-only view element for saving in the database.
 * This element is rendered for the front web.
 */
const defineDataDowncast = (editor) => {
  editor.conversion.for('dataDowncast').elementToElement({
    model: dataModel.name,
    view: (modelElement, { writer }) =>
      writer.createRawElement(dataView.name, { class: dataView.classes }, (domElement) => {
        domElement.innerHTML = ''; // TODO: render a div with the data params for Vue
        domElement.dataset[datasetAttributes.value] = dataParamStringify(modelElement.getAttribute(attributes.value));
        domElement.dataset[datasetAttributes.fieldId] = modelElement.getAttribute(attributes.fieldId);
      }),
  });
};

export default defineDataDowncast;
