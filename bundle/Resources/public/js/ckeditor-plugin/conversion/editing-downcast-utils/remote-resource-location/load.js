import { attributes, pluginKey } from '../../../constants';
import { enqueueLocationRequest, getEndpoint, reportError, request } from '../../../utils/location-requests';

const copy = (editor, selectedImage) => request(getEndpoint(editor, 'createLocation'), {
  method: 'POST',
  body: JSON.stringify({
    ...selectedImage,
    cropSettings: JSON.stringify(selectedImage.variations ?? {}),
    source: editor.config.get(pluginKey).source,
  }),
}).then(response => response.json());

/**
 * Loads the selected image of a stored element.
 *
 * With the `copyLocationsOnLoad` option, the element is switched to a copy of the stored location before it can be
 * edited, so changes made in the editor never reach the stored location, which other versions, translations or copies
 * of the content may reference. When loading or copying fails the element keeps the stored location unchanged.
 */
const loadLocation = ({ editor, model, locationId }) => {
  const { copyLocationsOnLoad } = editor.config.get(pluginKey);

  enqueueLocationRequest(model, async () => {
    let selectedImage;
    try {
      selectedImage = await request(getEndpoint(editor, 'getSelectedImage')(locationId)).then(response => response.json());
    } catch (error) {
      reportError(editor, 'ckeditor_location_load_error', error);

      return;
    }

    let editedLocationId = locationId;
    if (copyLocationsOnLoad) {
      try {
        ({ locationId: editedLocationId } = await copy(editor, selectedImage));
      } catch (error) {
        reportError(editor, 'ckeditor_location_copy_error', error);

        return;
      }
    }

    // Values restored on upcast, unless another resource has been selected in the meantime
    const currentValues = model.getAttribute(attributes.selectedImage);
    if (currentValues.id) {
      return;
    }

    const variationName = currentValues.selectedVariation?.label;

    // Not undoable: undo must not switch the element back to the stored location
    editor.model.enqueueChange({ isUndoable: false }, (writer) => {
      writer.setAttribute(attributes.locationId, editedLocationId, model);
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
