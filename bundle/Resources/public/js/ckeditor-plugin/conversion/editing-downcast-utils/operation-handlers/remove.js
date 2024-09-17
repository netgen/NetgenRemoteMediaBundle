import { attributes, pluginKey } from "../../../constants";
import deleteLocation from "../remote-resource-location/delete";

const handleRemove = ({ editor, operation }) => {
  if (
    operation.targetPosition.nodeAfter.name !== pluginKey ||
    operation.batch.operations[0].type === "detach"
  ) {
    return;
  }

  if (operation.targetPosition.nodeAfter.getAttribute(attributes.locationId)) {
    deleteLocation({ editor, model: operation.targetPosition.nodeAfter });
  }
};

export default handleRemove;
