import { attributes } from '../../constants';
import createLocation from './remote-resource-location/create';
import deleteLocation from './remote-resource-location/delete';
import updateLocation from './remote-resource-location/update';

const handleRemoteResourceLocation = ({ editor, model, domElement, eventDetail: { selectedImage, inputFields } }) => {
  selectedImage.cropSettings = domElement.querySelector(`[name="${inputFields.cropSettings}"]`)?.value ?? '';

  const oldImage = model.getAttribute(attributes.selectedImage);
  if (oldImage.id !== selectedImage.id) {
    if (oldImage.id) {
      deleteLocation({ editor, model });
    }

    if (selectedImage.id) {
      createLocation({ editor, model, selectedImage });
    }
  } else {
    updateLocation({ editor, model, selectedImage });
  }
};

export default handleRemoteResourceLocation;
