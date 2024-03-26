import { Command } from '@ckeditor/ckeditor5-core';

export class NetgenRemoteMediaCommand extends Command {
  refresh(...args) {
    this.isEnabled = !this.editor.isReadOnly;
    console.log("Command refresh, enabled: ", this.isEnabled);
  }

  execute() {
    console.log("Command execute");
  }
}
