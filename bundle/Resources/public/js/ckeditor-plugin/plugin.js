import { NetgenRemoteMediaCommand } from './command';
import { netgenRemoteMediaToolbarButton } from './button';

export const NetgenRemoteMedia = (editor) => {
  console.log("Plugin");

  editor.config.define('ngremotemedia', {});

  editor.commands.add('ngremotemedia', new NetgenRemoteMediaCommand(editor));

  editor.ui.componentFactory.add('ngremotemedia', () => netgenRemoteMediaToolbarButton(editor));
};
