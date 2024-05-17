const findElementsInModel = function (root, type) {
  const search = [...root.getChildren()];
  const nodes = [];

  while (search.length > 0) {
    const element = search.shift();

    if (element.name === type) {
      nodes.push(element);
    }

    if (element.childCount > 0) {
      for (const child of element.getChildren()) {
        if (child.name !== undefined) {
          search.push(child);
        }
      }
    }
  }

  return nodes;
};
