import { pluginKey } from './constants';

import NetgenRemoteMediaCommand from './command';
import getNetgenRemoteMediaToolbarButton from './button';

import setupDataCasting from './conversion';

const NetgenRemoteMediaPlugin = (editor) => {
  // editor.config.define(pluginKey, {});

  editor.commands.add(pluginKey, new NetgenRemoteMediaCommand(editor));
  editor.ui.componentFactory.add(pluginKey, () => getNetgenRemoteMediaToolbarButton(editor));

  editor.model.schema.register(pluginKey, {
    inheritAllFrom: '$blockObject',
    allowAttributes: ['value'],
  });

  setupDataCasting(editor);
};

export default NetgenRemoteMediaPlugin;
