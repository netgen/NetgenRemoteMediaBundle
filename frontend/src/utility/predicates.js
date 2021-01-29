export const not = f => item => !f(item);
export const equal = value => item => value === item;
export const truthy = value => !!value;
export const notUndefined = not(equal(undefined));
