import Vue from 'vue';
import { kebabToCamelCase } from './utility';

export const initDirective = {
  bind: function(el, binding, vnode) {
    const propertyName = kebabToCamelCase(binding.arg);

    if (propertyName === 'config' && binding.value) {
      // Use Vue.set to ensure reactivity for nested properties
      Object.keys(vnode.context[propertyName]).forEach(key => {
        if (!Object.prototype.hasOwnProperty.call(binding.value, key)) {
          Vue.delete(vnode.context[propertyName], key);
        }
      });

      Object.keys(binding.value).forEach(key => {
        Vue.set(vnode.context[propertyName], key, binding.value[key]);
      });
    } else {
      vnode.context[propertyName] = binding.value;
    }
  }
};
