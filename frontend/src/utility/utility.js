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

export const format = (num, decimals) => {
  const multiplyer = Math.pow(10, decimals);
  return parseFloat(Math.round(num * multiplyer) / multiplyer).toFixed(
    decimals
  );
};

const sizeDimensionsMap = {
  B: 'KB',
  KB: 'MB',
  MB: 'GB',
  GB: 'TB'
};
export const formatByteSize = (size, dim = 'B') => {
  const nextDim = sizeDimensionsMap[dim];

  if (!nextDim || size < 1024) {
    return `${format(size, 2)} ${dim}`;
  }

  return formatByteSize(size / 1024, nextDim);
};
