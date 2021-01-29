import { kebabToCamelCase } from './utility';

export const initDirective = {
  bind: function(el, binding, vnode) {
    const propertyName = kebabToCamelCase(binding.arg);

    vnode.context[propertyName] = binding.value;
  }
};
