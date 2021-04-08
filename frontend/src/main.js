import Vue from 'vue';
import './scss/ngremotemedia.scss';
import 'cropperjs/dist/cropper.css';
import './utility/polyfills';

Vue.config.productionTip = false;

const handleDOMContentLoaded = function() {
  console.log("from main.js");
};

if (
  document.readyState === 'complete' ||
  (document.readyState !== 'loading' && !document.documentElement.doScroll)
) {
  handleDOMContentLoaded();
} else {
  document.addEventListener('DOMContentLoaded', handleDOMContentLoaded);
}
