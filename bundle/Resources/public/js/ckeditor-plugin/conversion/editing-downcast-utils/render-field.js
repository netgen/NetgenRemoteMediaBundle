import { createElement } from '@ckeditor/ckeditor5-utils';
import { attributes, defaultValue, pluginKey } from '../../constants';

const renderField = ({ domElement, model, editor }) => {
  // eslint-disable-next-line no-param-reassign
  domElement.textContent = '';

  const pluginConfig = editor.config.get(pluginKey);
  const config = JSON.stringify(Object.assign({}, defaultValue.config, pluginConfig.config));
  const fieldId = model.getAttribute(attributes.fieldId);
  const selectedImage = JSON.stringify(model.getAttribute(attributes.selectedImage));
  domElement.append(
    createElement(
      domElement.ownerDocument,
      'div',
      {
        class: 'ngremotemedia-container',
        'data-id': fieldId,
        'v-init:selected-image': selectedImage,
        'v-init:config': config,
      },
      [
        createElement(domElement.ownerDocument, 'interactions', {
          'field-id': fieldId,
          ':selected-image': selectedImage,
          ':config': config,
        }),
      ],
    ),
  );

  const observer = new MutationObserver((_, observer) => {
    const image = domElement.querySelector('img');
    if (image !== null) {
      image.parentElement.style.textAlign = model.getAttribute('alignment') ?? '';
    }

    observer.disconnect();
  });
  observer.observe(domElement, { childList: true, subtree: true });
};

export default renderField;
