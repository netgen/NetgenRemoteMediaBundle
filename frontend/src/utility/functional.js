export const objectMap = f => obj => {
  return Object.keys(obj).reduce((newObj, property) => {
    newObj[property] = f(obj[property], property);
    return newObj;
  }, {});
};

export const objectFilter = p => obj => {
  return Object.keys(obj).reduce((newObj, property) => {
    if (p(obj[property], property)) {
      newObj[property] = obj[property];
    }
    return newObj;
  }, {});
};

export const constant = val => () => val;
