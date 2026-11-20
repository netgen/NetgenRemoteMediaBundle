import { attributes, pluginKey } from '../constants';
import { getEndpoint } from './location-requests';

/** Editors on the page, a location is kept while any of them references it */
const editors = new Set();

/** Locations each editor may delete once they are no longer in the editor data, by editor */
const deletableLocations = new WeakMap();

/** Locations each editor created or copied, by editor */
const ownLocations = new WeakMap();

const toKey = locationId => String(locationId);

export const registerEditor = (editor) => {
  editors.add(editor);
  deletableLocations.set(editor, new Set());
  ownLocations.set(editor, new Set());

  editor.on('destroy', () => editors.delete(editor));
};

/**
 * A location the editor created or copied: nothing else references it, so the editor changes it in place and
 * deletes it once it is no longer in the editor data.
 */
export const addOwnLocation = (editor, locationId) => {
  ownLocations.get(editor)?.add(toKey(locationId));
  deletableLocations.get(editor)?.add(toKey(locationId));
};

export const isOwnLocation = (editor, locationId) => ownLocations.get(editor)?.has(toKey(locationId)) ?? false;

/**
 * A location of the stored editor data, deleted once it is no longer in the editor data. Only for integrations
 * whose stored editor data is not shared (without `copyLocationsOnChange`).
 */
export const addStoredLocation = (editor, locationId) => {
  deletableLocations.get(editor)?.add(toKey(locationId));
};

const getReferencedLocations = () => {
  const referenced = new Set();

  editors.forEach((editor) => {
    editor.model.document.getRoots()
      .filter(root => root.rootName !== '$graveyard')
      .forEach((root) => {
        for (const item of editor.model.createRangeIn(root).getItems()) {
          if (item.is('element', pluginKey) && item.getAttribute(attributes.locationId)) {
            referenced.add(toKey(item.getAttribute(attributes.locationId)));
          }
        }
      });
  });

  return referenced;
};

/**
 * Deletes the locations of the given editors (by default all editors on the page) which are no longer referenced
 * by any editor on the page. Call it when the editor data is saved: until then, removed elements can come back
 * through undo, paste or drag and drop. Requests are sent with `keepalive`, so they complete when the page is
 * left right after, e.g. by the form submission.
 */
export const deleteUnusedLocations = (editorsToClean = [...editors]) => {
  const referenced = getReferencedLocations();
  const requests = [];

  editorsToClean.forEach((editor) => {
    const locations = deletableLocations.get(editor) ?? new Set();

    [...locations].filter(locationId => !referenced.has(locationId)).forEach((locationId) => {
      locations.delete(locationId);
      ownLocations.get(editor)?.delete(locationId);

      requests.push(fetch(getEndpoint(editor, 'deleteLocation')(locationId), { method: 'DELETE', keepalive: true }));
    });
  });

  return Promise.allSettled(requests);
};

/**
 * Deletes unused locations when the form containing the editor is submitted. Integrations which save the editor
 * data otherwise (e.g. with a request instead of the form submission) call `deleteUnusedLocations()` themselves.
 */
export const deleteUnusedLocationsOnSubmit = (editor) => {
  const form = editor.sourceElement?.closest?.('form');
  if (!form) {
    return;
  }

  form.addEventListener('submit', (event) => {
    if (event.defaultPrevented) {
      return;
    }

    deleteUnusedLocations([...editors].filter(otherEditor => form.contains(otherEditor.sourceElement)));
  });
};
