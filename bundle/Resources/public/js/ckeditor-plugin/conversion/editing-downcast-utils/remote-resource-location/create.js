import { attributes, pluginKey } from '../../../constants';
import { enqueueLocationRequest, getEndpoint, reportError, request } from '../../../utils/location-requests';
import { addOwnLocation } from '../../../utils/locations';

/**
 * Creates a location for the newly selected resource. When the request fails, the element goes back to the
 * previously selected resource and the editor is notified, instead of silently storing an element without a location.
 */
const createLocation = ({ editor, model, selectedImage, previousImage }) => {
  // Another resource may have been selected in the meantime, its own request sets the location
  const isStillSelected = () => model.getAttribute(attributes.selectedImage).id === selectedImage.id;

  enqueueLocationRequest(model, async () => {
    const response = await request(getEndpoint(editor, 'createLocation'), {
      method: 'POST',
      body: JSON.stringify({
        ...selectedImage,
        source: editor.config.get(pluginKey).source,
      }),
    });
    const { locationId } = await response.json();
    // Deleted once it is no longer in the editor data, also when another resource has been selected in the meantime
    addOwnLocation(editor, locationId);

    if (isStillSelected()) {
      editor.model.change((writer) => {
        writer.setAttribute(attributes.locationId, locationId, model);
      });
    }
  }).catch((error) => {
    if (isStillSelected()) {
      editor.model.change((writer) => {
        writer.setAttribute(attributes.selectedImage, previousImage, model);
      });
    }

    reportError(editor, 'ckeditor_location_create_error', error);
  });
};

export default createLocation;
