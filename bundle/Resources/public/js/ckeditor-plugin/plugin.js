import { dataModel, defaultValue, pluginKey } from './constants';

import NetgenRemoteMediaCommand from './command';
import getNetgenRemoteMediaToolbarButton from './button';

import setupDataCasting from './conversion';

const NetgenRemoteMediaPlugin = (editor) => {
  editor.config.define(pluginKey, {
    /** Editor field */
    fieldId: null,
    /** Netgen Remote Media configuration */
    config: null,
    /** Variation group for view */
    variationGroup: null,
    /** Source for remote resource locations */
    source: null,
    /** Endpoints for handling resources */
    endpoints: defaultValue.endpoints,
  });

  editor.commands.add(pluginKey, new NetgenRemoteMediaCommand(editor));
  editor.ui.componentFactory.add(pluginKey, () => getNetgenRemoteMediaToolbarButton(editor));

  editor.model.schema.register(pluginKey, {
    inheritAllFrom: '$blockObject',
    allowAttributes: dataModel.attributes,
  });

  setupDataCasting(editor);
};

export default NetgenRemoteMediaPlugin;
