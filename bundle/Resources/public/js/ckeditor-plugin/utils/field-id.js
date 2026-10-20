import { uid } from '@ckeditor/ckeditor5-utils';
import { pluginKey } from '../constants';

/**
 * Plugin `fieldId` config is either a string or a function receiving the editor instance,
 * so integrations with multiple editors on a page can derive an id per editor.
 */
export const resolveFieldId = (editor) => {
  const { fieldId } = editor.config.get(pluginKey);

  return typeof fieldId === 'function' ? fieldId(editor) : fieldId;
};

export const createFieldId = editor => `${resolveFieldId(editor)}_${uid()}`;
