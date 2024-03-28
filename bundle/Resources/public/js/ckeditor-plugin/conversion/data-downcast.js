/* eslint-disable no-console */
import { dataView, dataModel } from '../constants';

/**
 * Defines the data downcast conversion.
 * Data downcasting converts the model element to the read-only view element. Used when rendering content.
 */
const defineDataDowncast = (editor) => {
  editor.conversion.for('dataDowncast').elementToElement({
    model: dataModel.name,
    view: (modelElement, { writer }) =>
      writer.createRawElement(dataView.name, { class: dataView.classes }, (domElement) => {
        console.log({ modelElement, domElement, method: 'dataDowncast' });
        /* eslint-disable-next-line no-param-reassign */
        domElement.innerHTML = ''; // TODO: render a div with the data params for Vue
      }),
  });
};

export default defineDataDowncast;
