import Pickr from '@simonwep/pickr';

export default function UiColorPicker(
  el,
  onChange = 'advanced-color-did-change',
) {
  const pickr = new Pickr({
    el,
    theme: 'nano',
    closeOnScroll: true,
    useAsButton: true,
    inline: false,
    autoReposition: true,
    outputPrecision: 0,
    comparison: false,
    default: '#00000000',
    swatches: null,
    defaultRepresentation: 'HEX',
    showAlways: false,
    closeWithKey: 'Escape',
    position: 'bottom-middle',
    adjustableNumbers: true,
    components: {
      palette: false,
      preview: true,
      opacity: true,
      hue: true,
      interaction: {
        input: true,
      },
    },
  });

  // hydrate colour well on change
  pickr.on('change', (color) => {
    // notify icon editor
    const event = new CustomEvent(onChange, {
      detail: color.toHEXA().toString(),
    });
    window.dispatchEvent(event);
  });

  return pickr;
}
