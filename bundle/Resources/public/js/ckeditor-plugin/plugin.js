import { dataModel, pluginKey } from './constants';

import NetgenRemoteMediaCommand from './command';
import getNetgenRemoteMediaToolbarButton from './button';

import setupDataCasting from './conversion';

const NetgenRemoteMediaPlugin = (editor) => {
  editor.config.define(pluginKey, {
    variationGroup: null,
    fieldId: null,
    config: null,
    insertMediaEndpoint: null,
    removeMediaEndpoint: null,
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
