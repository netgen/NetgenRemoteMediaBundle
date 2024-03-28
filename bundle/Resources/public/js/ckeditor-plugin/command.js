import { Command } from '@ckeditor/ckeditor5-core';
import { pluginKey } from './constants';

class NetgenRemoteMediaCommand extends Command {
  refresh() {
    // TODO: based on document selection - only insert if nothing is selected, including an existing ngrm widget
    this.isEnabled = !this.editor.isReadOnly;
    this.value = {};
  }

  execute(value = {}) {
    const model = this.editor.model;
    const selection = model.document.selection;

    model.change((writer) => {
      console.log({ commandvalue: this.value, value });

      // If the command has a non-null value, there must be some HTML embed selected in the model.
      const element = writer.createElement(pluginKey);

      model.insertObject(element, null, null, { setSelection: 'on' });

      writer.setAttribute('value', value, element);
    });
  }
}

export default NetgenRemoteMediaCommand;
