const isPlainObject = value => Object.prototype.toString.call(value) === '[object Object]';

/**
 * Merges plain objects recursively, other values (including arrays) from `override` replace values from `base`.
 */
const mergeDeep = (base, override) => {
  const result = { ...base };

  Object.entries(override ?? {}).forEach(([key, value]) => {
    result[key] = isPlainObject(value) && isPlainObject(base[key]) ? mergeDeep(base[key], value) : value;
  });

  return result;
};

export default mergeDeep;
