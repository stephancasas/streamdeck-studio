import './bootstrap';
import Alpine from 'alpinejs';
import Theme from './theme';
import UiColor from './uicolor';
import UiColorPicker from './uicolorpicker';
import IconCollection from './icon-collection';
import IconEditor from './icon-editor';
import JSZip from 'jszip';

window.UiColorPicker = UiColorPicker;
window.UiColor = UiColor;
window.JSZip = JSZip;
window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
  Alpine.data('Theme', Theme);
  Alpine.data('IconEditor', IconEditor);
  Alpine.data('IconCollection', IconCollection);
});

Alpine.start();
