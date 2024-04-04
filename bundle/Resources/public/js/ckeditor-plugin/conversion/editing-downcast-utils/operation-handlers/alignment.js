import { attributes } from "../../../constants";

const handleAlignment = ({ selectedElement, editor, action }) => {
    const fieldId = selectedElement.getAttribute(attributes.fieldId)
    const ngremotemediaParent = editor.sourceElement.nextElementSibling.querySelector(`[${attributes.fieldId}=${fieldId}]`);

    const image = ngremotemediaParent.querySelector('img')?.parentElement;

    if (image === null) {
      return;
    }

    image.style.textAlign = action.operations[0]?.newValue ?? '';
};

export default handleAlignment;
