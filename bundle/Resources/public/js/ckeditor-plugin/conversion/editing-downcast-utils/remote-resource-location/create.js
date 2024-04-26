import { attributes, defaultValue, pluginKey } from '../../../constants';

const createLocation = ({ editor, model, selectedImage }) => {
  const createLocationEndpoint = editor.config.get(pluginKey).endpoints.createLocation ?? defaultValue.endpoints.createLocation;

  fetch(createLocationEndpoint, {
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
