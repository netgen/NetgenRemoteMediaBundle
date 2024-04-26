import { attributes, defaultValue, pluginKey } from '../../../constants';

const deleteLocation = ({ editor, model }) => {
  const deleteLocationEndpoint = editor.config.get(pluginKey).endpoints.deleteLocation ?? defaultValue.endpoints.deleteLocation;

  fetch(deleteLocationEndpoint(model.getAttribute(attributes.locationId)), {
    method: 'DELETE',
  });
};

export default deleteLocation;
