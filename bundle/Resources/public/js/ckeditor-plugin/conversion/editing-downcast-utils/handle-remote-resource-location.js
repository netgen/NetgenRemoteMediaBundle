import { attributes } from '../../constants';
import createLocation from './remote-resource-location/create';
import updateLocation from './remote-resource-location/update';

/**
 * Locations are never deleted from the editor: removed, replaced or cut elements can come back through undo or paste,
 * and the stored editor data may still reference them until it is saved.
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
