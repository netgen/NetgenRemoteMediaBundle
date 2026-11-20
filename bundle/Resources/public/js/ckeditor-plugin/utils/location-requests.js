import { defaultValue, pluginKey } from '../constants';
import mergeDeep from './merge-deep';

/** Location requests of all editors on the page which have not settled yet */
const pendingRequests = new Set();

/** Last queued location request per model element, requests of one element run one after another */
const elementQueues = new WeakMap();

/**
 * Resolves when all location requests started so far have settled. Integrations wait for it before submitting
 * editor data, so an element whose location is still being created is not stored without its location.
 */
export const waitForLocationRequests = () => Promise.allSettled([...pendingRequests]);

export const hasPendingLocationRequests = () => pendingRequests.size > 0;

/**
 * Runs `task` after the previous location request of the same model element has settled, so for example an update
 * always targets the location created or copied by the request before it.
 */
export const enqueueLocationRequest = (model, task) => {
  const request = (elementQueues.get(model) ?? Promise.resolve()).then(task);
  const settled = request.catch(() => {});

  elementQueues.set(model, settled);
  pendingRequests.add(settled);
  settled.then(() => pendingRequests.delete(settled));

  return request;
};

/** Fetch which rejects on HTTP error responses too */
export const request = async (url, options = {}) => {
  const response = await fetch(url, options);

  if (!response.ok) {
    throw new Error(`Request ${options.method ?? 'GET'} ${url} failed with status ${response.status}`);
  }

  return response;
};

export const getEndpoint = (editor, name) => editor.config.get(pluginKey).endpoints?.[name] ?? defaultValue.endpoints[name];

/** Reports a failed location request through the `onError` plugin option, with a translated message */
export const reportError = (editor, translationKey, error) => {
  const pluginConfig = editor.config.get(pluginKey);
  const message = mergeDeep(defaultValue.config, pluginConfig.config).translations[translationKey] ?? translationKey;

  if (typeof pluginConfig.onError === 'function') {
    pluginConfig.onError(message, error);

    return;
  }

  console.error(message, error);
};
