import { attributes, pluginKey } from '../../../constants';

const createLocation = ({ editor, model, selectedImage }) => {
  fetch(editor.config.get(pluginKey).endpoints.createLocation, {
    method: 'POST',
    body: JSON.stringify(selectedImage),
  })
    .then(response => response.json())
    .then(({ locationId }) => {
      editor.model.change((writer) => {
        writer.setAttribute(attributes.locationId, locationId, model);
      });
    });
};

export default createLocation;
