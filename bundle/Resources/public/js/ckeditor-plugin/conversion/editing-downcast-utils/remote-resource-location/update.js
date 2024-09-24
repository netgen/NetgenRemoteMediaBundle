import { attributes, defaultValue, pluginKey } from '../../../constants';

const updateLocation = ({ editor, model, selectedImage }) => {
  const updateLocationEndpoint = editor.config.get(pluginKey).endpoints.updateLocation ?? defaultValue.endpoints.updateLocation;

  fetch(updateLocationEndpoint(model.getAttribute(attributes.locationId)), {
    method: 'PUT',
    body: JSON.stringify(selectedImage),
  });
};

export default updateLocation;
