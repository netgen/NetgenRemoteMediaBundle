import { pluginKey } from '../constants';

/**
 * Plugin `fieldId` config is either a string or a function receiving the editor instance,
 * so integrations with multiple editors on a page can derive an id per editor.
 */
export const resolveFieldId = (editor) => {
  const { fieldId } = editor.config.get(pluginKey);

  return typeof fieldId === 'function' ? fieldId(editor) : fieldId;
};

/**
 * Random hex suffix. Generated locally instead of using `uid` from `@ckeditor/ckeditor5-utils`:
 * integrations aliasing CKEditor packages to a prebuilt build (e.g. Sylius) don't expose `uid`.
 * `crypto.getRandomValues` is used because `crypto.randomUUID` needs a secure context (HTTPS).
 * The suffix must be random, not a counter: stored content keeps its field ids across page loads.
 */
const randomSuffix = () => Array.from(
  crypto.getRandomValues(new Uint8Array(16)),
  byte => byte.toString(16).padStart(2, '0'),
).join('');

export const createFieldId = editor => `${resolveFieldId(editor)}_${randomSuffix()}`;
