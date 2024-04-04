import { pluginKey } from "../../../constants";

const handleRemove = ({ operation }) => {
    if (operation.targetPosition.nodeAfter.name !== pluginKey) {
        return;
    }
    
    // TODO: delete from backend
    console.log("Deleted ngremotemedia element");
};

export default handleRemove;
