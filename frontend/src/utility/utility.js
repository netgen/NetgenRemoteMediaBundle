export const encodeQueryData = data => {
  const ret = [];
  for (let d in data)
    ret.push(encodeURIComponent(d) + '=' + encodeURIComponent(data[d]));
  return ret.join('&');
};

export const capitalizeFirstLetter = str => str[0].toUpperCase() + str.slice(1);

export const kebabToCamelCase = str => {
  const [first, ...rest] = str.split('-');
  return [first, ...rest.map(capitalizeFirstLetter)].join('');
};
