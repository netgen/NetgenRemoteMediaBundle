/* eslint-disable no-console */
import { dataView, dataModel } from '../constants';

/**
 * Defines the upcast conversion.
 * Upcasting converts the editing view element to the model element. Used when editing content.
 */
const defineUpcast = (editor) => {
  editor.conversion.for('upcast').elementToElement({
    view: dataView,
    // TODO: figure this out, mine won't be raw content
    // The div.raw-html-embed is registered as a raw content element,
    // so all it's content is available in a custom property.
    model(viewElement, { writer }) {
      console.log({ viewElement, method: 'upcast' });
      writer.createElement(dataModel.name, {
        value: viewElement.getCustomProperty('$rawContent'), // TODO: needs to be the remotemedia object like in akeneo that translates into the data attr
      });
    },
  });
};

export default defineUpcast;
