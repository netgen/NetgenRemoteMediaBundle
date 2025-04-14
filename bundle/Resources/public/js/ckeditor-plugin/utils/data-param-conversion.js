export const dataParamStringify = object => JSON.stringify(object).replace(/"/g, '&quot;');
export const dataParamParse = string => JSON.parse(string.replace(/&quot;/g, '"'));
