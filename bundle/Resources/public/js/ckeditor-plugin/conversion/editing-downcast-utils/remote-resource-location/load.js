import { attributes, pluginKey } from '../../../constants';
import { enqueueLocationRequest, getEndpoint, reportError, request } from '../../../utils/location-requests';
import { addStoredLocation } from '../../../utils/locations';

/**
 * Loads the selected image of a stored element. When loading fails the element keeps the stored location unchanged.
 *
 * Without the `copyLocationsOnChange` option, the stored location belongs to this editor data only, so it is deleted
 * once the element is no longer in the saved data. With it, the stored location is never changed nor deleted, see
 * `update.js`.
 */
const loadLocation = ({ editor, model, locationId }) => {
  if (!editor.config.get(pluginKey).copyLocationsOnChange) {
    addStoredLocation(editor, locationId);
  }

  enqueueLocationRequest(model, async () => {
    let selectedImage;
    try {
      selectedImage = await request(getEndpoint(editor, 'getSelectedImage')(locationId)).then(response => response.json());
    } catch (error) {
      reportError(editor, 'ckeditor_location_load_error', error);

      return;
    }

    // Values restored on upcast, unless another resource has been selected in the meantime
    const currentValues = model.getAttribute(attributes.selectedImage);
    if (currentValues.id) {
      return;
    }

    const variationName = currentValues.selectedVariation?.label;

    // Not undoable: undo must not switch the element back to an image which isn't loaded
    editor.model.enqueueChange({ isUndoable: false }, (writer) => {
      writer.setAttribute(
        attributes.selectedImage,
        {
          ...selectedImage,
          cssClass: currentValues.cssClass,
          selectedVariation: variationName
            ? { label: variationName, value: selectedImage.variations?.[variationName] }
            : null,
        },
        model,
      );
    });
  });
};

export default loadLocation;
