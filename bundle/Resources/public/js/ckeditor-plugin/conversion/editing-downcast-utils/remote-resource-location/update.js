import { attributes, pluginKey } from '../../../constants';

const updateLocation = ({ editor, model, selectedImage }) => {
  fetch(editor.config.get(pluginKey).endpoints.updateLocation(model.getAttribute(attributes.locationId)), {
    method: 'PUT',
    body: JSON.stringify(selectedImage),
  });
};

export default updateLocation;
