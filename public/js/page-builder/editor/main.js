import { loadState } from './state.js';
import { VisualEditor } from './editor.js';

const editor = new VisualEditor(loadState());
editor.init();
