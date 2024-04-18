import { attributes, pluginKey } from '../../../constants';

const deleteLocation = ({ editor, model }) => {
  fetch(editor.config.get(pluginKey).endpoints.deleteLocation(model.getAttribute(attributes.locationId)), {
    method: 'DELETE',
  });
};

export default deleteLocation;
