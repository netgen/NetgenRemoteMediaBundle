import { Command } from '@ckeditor/ckeditor5-core';
import { pluginKey, defaultValue, attributes } from './constants';

class NetgenRemoteMediaCommand extends Command {
  constructor(editor) {
    super(editor);

    this.value = 0;
  }

  refresh() {
    const selectedElement =
      this.editor.model.document.selection.getSelectedElement();
    const isRemoteMedia = selectedElement?.name === pluginKey;

    this.isEnabled = !this.editor.isReadOnly && !isRemoteMedia;
  }

  execute() {
    if (!this.isEnabled) {
      return;
    }

    this.value += 1;

    this.editor.model.change((writer) => {
      const element = writer.createElement(pluginKey);

      this.editor.model.insertObject(element, null, null, {
        setSelection: 'on',
      });

      writer.setAttribute(
        attributes.fieldId,
        `${this.editor.config.get(pluginKey).fieldId}_${this.value}`,
        element
      );
      writer.setAttribute(
        attributes.selectedImage,
        defaultValue.selectedImage,
        element
      );
      writer.setAttribute(
        attributes.locationId,
        null,
        element
      );
    });
  }
}

export default NetgenRemoteMediaCommand;
