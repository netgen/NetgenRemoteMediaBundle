import { attributes, pluginKey } from '../../../constants';
import { enqueueLocationRequest, getEndpoint, reportError, request } from '../../../utils/location-requests';
import { addOwnLocation, isOwnLocation } from '../../../utils/locations';

/**
 * Saves changes of the element to its location.
 *
 * With the `copyLocationsOnChange` option, a stored location (which other versions, translations or copies of the
 * stored data may reference) is never changed: the first change of the element creates a copy of the location with
 * the change, and the element is switched to the copy. Opening the editor without changing anything creates nothing.
 */
const updateLocation = ({ editor, model, selectedImage }) => {
  const { copyLocationsOnChange, source } = editor.config.get(pluginKey);
  const body = JSON.stringify({ ...selectedImage, source });

  enqueueLocationRequest(model, async () => {
    // Read when the request runs: a preceding create or copy request sets the location of the element
    const locationId = model.getAttribute(attributes.locationId);
    if (!locationId) {
      return;
    }

    if (!copyLocationsOnChange || isOwnLocation(editor, locationId)) {
      await request(getEndpoint(editor, 'updateLocation')(locationId), { method: 'PUT', body });

      return;
    }

    const response = await request(getEndpoint(editor, 'createLocation'), { method: 'POST', body });
    const { locationId: copyId } = await response.json();
    addOwnLocation(editor, copyId);

    if (model.getAttribute(attributes.locationId) !== locationId) {
      return;
    }

    // Not undoable: undo must not switch the element back to the stored location
    editor.model.enqueueChange({ isUndoable: false }, (writer) => {
      writer.setAttribute(attributes.locationId, copyId, model);
    });
  }).catch(error => reportError(editor, 'ckeditor_location_update_error', error));
};

export default updateLocation;
