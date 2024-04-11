import { defaultValue, pluginKey } from "../../../constants";

const handleRemove = ({ editor, operation }) => {
  if (operation.targetPosition.nodeAfter.name !== pluginKey) {
    return;
  }
  // if element has location id attribute set

  // TODO: delete from backend
  console.group("Deleted ngremotemedia element");
  console.log(operation.targetPosition.nodeAfter);
  console.groupEnd();
};

export default handleRemove;
