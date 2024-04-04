import { pluginKey } from "../../../constants";

const handleRemove = ({ editor, operation }) => {
  if (operation.targetPosition.nodeAfter.name !== pluginKey) {
    return;
  }

  if (!editor.config.get(pluginKey).removeMediaEndpoint) {
    return;
  }
  // if element has location id attribute set

  // TODO: delete from backend
  console.log("Deleted ngremotemedia element");
};

export default handleRemove;
