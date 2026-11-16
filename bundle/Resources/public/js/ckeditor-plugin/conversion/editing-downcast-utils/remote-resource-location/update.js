import { attributes, pluginKey } from '../../../constants';
import { enqueueLocationRequest, getEndpoint, reportError, request } from '../../../utils/location-requests';

const updateLocation = ({ editor, model, selectedImage }) => {
  enqueueLocationRequest(model, () => {
    // Read when the request runs: a preceding create or copy request sets the location of the element
    const locationId = model.getAttribute(attributes.locationId);
    if (!locationId) {
      return null;
    }

    return request(getEndpoint(editor, 'updateLocation')(locationId), {
      method: 'PUT',
      body: JSON.stringify({
        ...selectedImage,
        source: editor.config.get(pluginKey).source,
      }),
    });
  }).catch(error => reportError(editor, 'ckeditor_location_update_error', error));
};

export default updateLocation;
