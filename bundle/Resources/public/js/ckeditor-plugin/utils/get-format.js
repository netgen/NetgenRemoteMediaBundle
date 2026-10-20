import { pluginKey } from '../constants';
import htmlFormat from '../formats/html';

const getFormat = editor => editor.config.get(pluginKey).format ?? htmlFormat;

export default getFormat;
