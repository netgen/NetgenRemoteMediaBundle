import { attributes } from '../../constants';
import createLocation from './remote-resource-location/create';
import updateLocation from './remote-resource-location/update';

/**
 * Locations are not deleted when an element is removed or its resource replaced: the element can come back through
 * undo, paste or drag and drop. Locations which are no longer in the editor data are deleted when the data is saved,
 * see `utils/locations.js`.
 */
const handleRemoteResourceLocation = ({ editor, model, domElement, eventDetail: { selectedImage, inputFields } }) => {
  selectedImage.cropSettings = domElement.querySelector(`[name="${inputFields.cropSettings}"]`)?.value ?? '';

  const previousImage = model.getAttribute(attributes.selectedImage);
  if (previousImage.id === selectedImage.id) {
    if (selectedImage.id) {
      updateLocation({ editor, model, selectedImage });
    }

    return;
  }

  if (selectedImage.id) {
    createLocation({ editor, model, selectedImage, previousImage });

    return;
  }

  // Resource removed from the element, which then no longer references a location
  editor.model.change((writer) => {
    writer.setAttribute(attributes.locationId, null, model);
  });
};

export default handleRemoteResourceLocation;
