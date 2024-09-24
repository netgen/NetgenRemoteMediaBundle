import defineUpcast from './conversion/upcast';
import defineDataDowncast from './conversion/data-downcast';
import defineEditingDowncast from './conversion/editing-downcast';

const setupDataCasting = (editor) => {
  defineUpcast(editor);
  defineDataDowncast(editor);
  defineEditingDowncast(editor);
};

export default setupDataCasting;
