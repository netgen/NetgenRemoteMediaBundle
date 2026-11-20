import { dataModel, defaultValue, pluginKey } from './constants';

import NetgenRemoteMediaCommand from './command';
import getNetgenRemoteMediaToolbarButton from './button';

import setupDataCasting from './conversion';
import { deleteUnusedLocationsOnSubmit, registerEditor } from './utils/locations';

// A regular function, not an arrow function: CKEditor instantiates function plugins with `new`
function NetgenRemoteMediaPlugin(editor) {
  editor.config.define(pluginKey, {
    /** Editor field, string or `(editor) => string` */
    fieldId: null,
    /** Netgen Remote Media configuration, deep merged over the defaults */
    config: null,
    /** Variation group for view */
    variationGroup: null,
    /** Source for remote resource locations */
    source: null,
    /** Endpoints for handling resources */
    endpoints: defaultValue.endpoints,
    /** Storage format of the remote media element in editor data, defaults to `formats/html.js` */
    format: null,
    /**
     * Never change stored locations, for integrations which store editor data in versions, translations or copies
     * sharing the same locations: the first change of an element creates a copy of its location, and the element
     * is switched to the copy. Stored locations are then never deleted by the editor either.
     */
    copyLocationsOnChange: false,
    /** Called with a translated message and the error when a location request fails, defaults to `console.error` */
    onError: null,
  });

  editor.commands.add(pluginKey, new NetgenRemoteMediaCommand(editor));
  editor.ui.componentFactory.add(pluginKey, () => getNetgenRemoteMediaToolbarButton(editor));

  editor.model.schema.register(pluginKey, {
    inheritAllFrom: '$blockObject',
    allowAttributes: dataModel.attributes,
  });

  setupDataCasting(editor);

  // Locations the editor created, copied or loaded are deleted when the data is saved and no longer references them
  registerEditor(editor);
  deleteUnusedLocationsOnSubmit(editor);
}

export default NetgenRemoteMediaPlugin;
