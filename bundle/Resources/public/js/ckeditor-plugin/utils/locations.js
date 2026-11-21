import { attributes, pluginKey } from '../constants';
import { getEndpoint } from './location-requests';

/** Editors on the page, a location is kept while any of them references it */
const editors = new Set();

/** Locations each editor may delete once they are no longer in the editor data, by editor */
const deletableLocations = new WeakMap();

/** Locations each editor created or copied, by editor */
const ownLocations = new WeakMap();

/** Locations of the stored editor data each editor loaded, by editor */
const storedLocations = new WeakMap();

const toKey = locationId => String(locationId);

export const registerEditor = (editor) => {
  editors.add(editor);
  deletableLocations.set(editor, new Set());
  ownLocations.set(editor, new Set());
  storedLocations.set(editor, new Set());

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
 * A location of the stored editor data. Without `copyLocationsOnChange` the stored editor data is not shared, so the
 * editor deletes the location once it is no longer in the editor data. With it, other stored data may reference the
 * location, so it is only reported by `getUnusedStoredLocations()`.
 */
export const addStoredLocation = (editor, locationId) => {
  if (editor.config.get(pluginKey).copyLocationsOnChange) {
    storedLocations.get(editor)?.add(toKey(locationId));

    return;
  }

  deletableLocations.get(editor)?.add(toKey(locationId));
};

/** Editors in the given form, or all editors on the page */
const getEditors = form => [...editors].filter(editor => form === null || form.contains(editor.sourceElement));

/** Locations referenced by the data of the given editors, by default of all editors on the page */
const getReferencedLocations = (referencingEditors = editors) => {
  const referenced = new Set();

  referencingEditors.forEach((editor) => {
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
 * Deletes the locations of the editors in the given form (by default of all editors on the page) which are no longer
 * referenced by any editor on the page. Call it when the editor data is saved: until then, removed elements can come
 * back through undo, paste or drag and drop. Requests are sent with `keepalive`, so they complete when the page is
 * left right after, e.g. by the form submission.
 */
export const deleteUnusedLocations = (form = null) => {
  const referenced = getReferencedLocations();
  const requests = [];

  getEditors(form).forEach((editor) => {
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
 * Deletes all locations the editors in the given form (by default all editors on the page) created or copied, also
 * those still in the editor data, unless an editor outside the form references them. Call it when the editor data is
 * discarded (e.g. a cancelled edit): the locations were never saved with the data, so nothing else deletes them.
 * Requests are sent with `keepalive`, so they complete when the page is left right after.
 */
export const deleteOwnLocations = (form = null) => {
  const formEditors = getEditors(form);
  const referenced = getReferencedLocations([...editors].filter(editor => !formEditors.includes(editor)));
  const requests = [];

  formEditors.forEach((editor) => {
    const locations = ownLocations.get(editor) ?? new Set();

    [...locations].filter(locationId => !referenced.has(locationId)).forEach((locationId) => {
      locations.delete(locationId);
      deletableLocations.get(editor)?.delete(locationId);

      requests.push(fetch(getEndpoint(editor, 'deleteLocation')(locationId), { method: 'DELETE', keepalive: true }));
    });
  });

  return Promise.allSettled(requests);
};

/**
 * With `copyLocationsOnChange`: IDs of the stored locations the editors in the given form (by default all editors on
 * the page) loaded, which are no longer referenced by any editor on the page (e.g. replaced by copies, or of removed
 * elements). The editor never deletes them, as other stored data may reference them: integrations send them along
 * with the saved data, so the server deletes those which nothing references anymore once the data is saved.
 */
export const getUnusedStoredLocations = (form = null) => {
  const referenced = getReferencedLocations();
  const unused = new Set();

  getEditors(form).forEach((editor) => {
    storedLocations.get(editor)?.forEach((locationId) => {
      if (!referenced.has(locationId)) {
        unused.add(locationId);
      }
    });
  });

  return [...unused];
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
    if (!event.defaultPrevented) {
      deleteUnusedLocations(form);
    }
  });
};
