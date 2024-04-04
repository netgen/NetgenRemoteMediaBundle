import { attributes, pluginKey } from "../../../constants";

const handleAlignment = ({ event, editor, operation }) => {
    const selectedElement = event.source.selection.getSelectedElement();
    if (selectedElement?.name !== pluginKey) {
        return;
    }

    const fieldId = selectedElement.getAttribute(attributes.fieldId)
    const ngremotemediaParent = editor.sourceElement.nextElementSibling.querySelector(`[${attributes.fieldId}=${fieldId}]`);

    const image = ngremotemediaParent.querySelector('img')?.parentElement;

    if (image === null) {
      return;
    }

    image.style.textAlign = operation.newValue ?? '';
};

export default handleAlignment;
