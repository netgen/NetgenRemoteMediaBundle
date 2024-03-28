import { createElement } from '@ckeditor/ckeditor5-utils';

const renderField = (domElement) => {
  // Remove all children;
  // eslint-disable-next-line no-param-reassign
  domElement.textContent = '';

  console.log({ owner: domElement.ownerDocument, domElement });

  domElement.append(
    createElement(domElement.ownerDocument, 'input', {
      type: 'hidden',
      name: 'form[remoteMedia][locationId]',
      value: '3446123',
    }),
  );
  domElement.append(
    createElement(domElement.ownerDocument, 'input', {
      type: 'hidden',
      name: 'form[remoteMedia][source]',
      value: 'what even',
    }),
  );
  domElement.append(
    createElement(
      domElement.ownerDocument,
      'div',
      {
        class: 'ngremotemedia-container',
        'data-id': 'test',
        'v-init:selected-image': '{test:123}',
        'v-init:config': '{test:321}',
      },
      [
        createElement(domElement.ownerDocument, 'interactions', {
          'field-id': 'test',
          ':config': '{test:321}',
          ':selected-image': '{test:123}',
        }),
      ],
    ),
  );
};

export default renderField;
