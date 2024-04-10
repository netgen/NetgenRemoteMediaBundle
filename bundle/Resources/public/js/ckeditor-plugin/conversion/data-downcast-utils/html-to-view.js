const findViewParent = (viewElements, childIndex) => {
    let parentView = null;
    childIndex.split('.').slice(0, -1).forEach((viewElementIndex) => {
        if (parentView === null) {
            parentView = viewElements[viewElementIndex] ?? null;

            return;
        }

        parentView = parentView.getChild(viewElementIndex);
    });

    return parentView;
};

let domParser = null;
function htmlToView(htmlString, writer) {
  if (domParser === null) {
    domParser = new DOMParser();
  }

  const viewElements = [];

  const htmlDocument = domParser.parseFromString(htmlString, 'text/html');
  const htmlElements = [];
  htmlDocument.querySelectorAll('body > *').forEach((element, index) => {
    htmlElements.push({ element, index: index.toString() });
  });
  while (htmlElements.length > 0) {
    const { element, index } = htmlElements.shift();

    let childIndex = 0;
    Object.entries(element.childNodes).forEach(([_, childNode]) => {
      if (childNode.data?.trim().length === 0) {
        return;
      }

      htmlElements.push({
        element: childNode,
        index: `${index}.${childIndex}`,
      });
      childIndex++;
    });

    const viewAttributes = {};
    if (element.attributes) {
      for (const attribute of element.attributes) {
          viewAttributes[attribute.name] = attribute.value;
      }
    }

    let createElement = null;
    let elementValue = null;
    if (element.childNodes.length > 0) {
      createElement = writer.createContainerElement;
      elementValue = element.nodeName.toLowerCase();
    } else if (element.nodeName === '#text') {
      createElement = writer.createText;
      elementValue = element.data;
    } else {
      createElement = writer.createEmptyElement;
      elementValue = element.nodeName.toLowerCase();
    }
    const viewElement = createElement.bind(writer)(
      elementValue,
      viewAttributes,
    );

    const parentView = findViewParent(viewElements, index);

    if (parentView !== null) {
        parentView._appendChild(viewElement);
    } else {
        viewElements.push(viewElement);
    }
  }

  return viewElements;
};

export default htmlToView;
