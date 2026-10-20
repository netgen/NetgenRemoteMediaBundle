import { Command } from '@ckeditor/ckeditor5-core';
import { pluginKey, defaultValue, attributes } from './constants';
import { createFieldId } from './utils/field-id';

class NetgenRemoteMediaCommand extends Command {
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

    this.editor.model.change((writer) => {
      const element = writer.createElement(pluginKey);

      this.editor.model.insertObject(element, null, null, {
        setSelection: 'on',
      });

      writer.setAttribute(
        attributes.fieldId,
        createFieldId(this.editor),
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
