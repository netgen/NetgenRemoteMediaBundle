import { dataView, viewAttributes, defaultValue, alignments } from '../constants';

/**
 * Default storage format: a self-describing `div` which is rendered on the front by `render-views.js`.
 *
 * A format defines how the remote media model element is stored in the editor data (HTML):
 *  - `matcher`: CKEditor view matcher pattern for elements handled by the format
 *  - `priority`: upcast converter priority, raise it to take precedence over other converters matching the same elements
 *  - `fromView(viewElement)`: reads `{ fieldId, locationId, cssClass, variationName, alignment }` from the stored element
 *  - `toView(writer, data, pluginConfig)`: creates the stored view element from the same data, it must always
 *    return a view element (CKEditor cannot map positions of siblings after an unconverted element)
 */
const htmlFormat = {
  matcher: dataView,
  priority: 'normal',
  fromView: viewElement => ({
    fieldId: viewElement.getAttribute(viewAttributes.fieldId),
    locationId: viewElement.getAttribute(viewAttributes.locationId),
    cssClass: viewElement.getAttribute(viewAttributes.cssClass),
    variationName: viewElement.getAttribute(viewAttributes.variationName),
    // Content saved by earlier plugin versions stores `"undefined"` for elements without alignment
    alignment: alignments.includes(viewElement.getAttribute(viewAttributes.alignment))
      ? viewElement.getAttribute(viewAttributes.alignment)
      : null,
  }),
  toView: (writer, data, pluginConfig) => {
    const viewResourceEndpoint = pluginConfig.endpoints.viewResource ?? defaultValue.endpoints.viewResource;

    return writer.createContainerElement(
      dataView.name,
      {
        class: dataView.classes,
        style: data.alignment ? `text-align:${data.alignment};` : null,
        [viewAttributes.fieldId]: data.fieldId,
        [viewAttributes.locationId]: data.locationId,
        [viewAttributes.cssClass]: data.cssClass,
        [viewAttributes.variationName]: data.variationName,
        [viewAttributes.variationGroup]: pluginConfig.variationGroup,
        [viewAttributes.viewEndpoint]: viewResourceEndpoint(data.locationId),
        [viewAttributes.alignment]: data.alignment,
      },
    );
  },
};

export default htmlFormat;
