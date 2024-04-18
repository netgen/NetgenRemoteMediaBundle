import { viewAttributes, dataView } from "./constants";

const renderViews = () => {
  document.querySelectorAll(`.${dataView.classes}`).forEach(async (ngrmElement) => {
    const queryParams = [];
    if (ngrmElement.getAttribute(viewAttributes.cssClass)) {
      queryParams.push(`css_class=${ngrmElement.getAttribute(viewAttributes.cssClass)}`);
    }
    if (ngrmElement.getAttribute(viewAttributes.variationGroup)) {
      queryParams.push(`variation_group=${ngrmElement.getAttribute(viewAttributes.variationGroup)}`);
    }
    if (ngrmElement.getAttribute(viewAttributes.variationName)) {
      queryParams.push(`variation_name=${ngrmElement.getAttribute(viewAttributes.variationName)}`);
    }
    if (ngrmElement.getAttribute(viewAttributes.alignment)) {
      queryParams.push(`alignment=${ngrmElement.getAttribute(viewAttributes.alignment)}`);
    }

    let url = ngrmElement.getAttribute(viewAttributes.viewEndpoint);
    if (queryParams.length) {
      url += `?${queryParams.join('&')}`;
    }

    const renderedView = await fetch(url).then((response) => {
      if (!response.ok) {
        console.error(response.error);
      }

      return response.text();
    });

    ngrmElement.innerHTML = renderedView;
  });
};

const render = () => {
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", renderViews);
  } else {
    renderViews();
  }
};

export default render;
