import Html2Canvas from 'html2canvas';
import UiColorPicker from './uicolorpicker';

export default function () {
  let $refs, $watch, $wire;

  const renderIconToImage = () => {
    Html2Canvas($refs.iconCanvasForRender, {
      backgroundColor: null,
      logging: false,
    }).then((canvasEl) => {
      $refs.pngPreview.setAttribute('src', canvasEl.toDataURL());
    });
  };

  const watchForRenderEvents = () => {
    let debounce;
    const debounced = () => {
      clearTimeout(debounce);
      debounce = setTimeout(renderIconToImage, 500);
    };

    // label and labelVisibility are wire-deferred
    $watch('label', debounced);
    $watch('labelVisibility', debounced);

    // advanced color is local
    $watch('advancedColor', debounced);

    // all other props listen to livewire
    $wire.on('icon-did-update', debounced);
  };

  const downloadCurrent = (label) => {
    $wire.call('telemetry', 'icon-export-download');

    const a = document.createElement('a');
    a.href = $refs.pngPreview.getAttribute('src');
    a.download = `${label.replace(/\s/g, '-').replace(/\./g, '')}.png`;
    a.click();
  };

  const syncTailwindToAdvanced = (context, advColorUi) => {
    context.advancedColor.glyph = UiColor.resolveTailwindColor(
      `bg-${$wire.glyphColor}`,
    );
    advColorUi.glyph.setColor(context.advancedColor.glyph);

    context.advancedColor.canvas = UiColor.resolveTailwindColor(
      `bg-${$wire.canvasColor}`,
    );
    advColorUi.canvas.setColor(context.advancedColor.canvas);

    context.advancedColor.label = UiColor.resolveTailwindColor(
      `bg-${$wire.labelColor}`,
    );
    advColorUi.label.setColor(context.advancedColor.label);
  };

  return {
    init() {
      $refs = this.$refs;
      $wire = this.$wire;
      $watch = this.$watch;

      watchForRenderEvents();
      setTimeout(renderIconToImage);

      /**
       * delay display of icon canvas for slow browser font processing
       * -
       * use goofy style injection method because Html2Canvas does this weird
       * page reload thing and drops context for alpine.js and globalThis
       */
      setTimeout(() => {
        const style = document.createElement('style');
        style.innerHTML = `
        #icon-canvas-aggregate {
            opacity: 1 !important;
            transform: unset !important;
        }`;
        document.head.appendChild(style);
      }, 700);

      // this next part is lazy but i'm not being paid so i do not care about it

      const advColorUi = {};
      // prettier-ignore
      {
        advColorUi.glyph = UiColorPicker(
          '#advanced-color-glyph',
          'advanced-glyph-color-did-update',
        );
        window.addEventListener(
          'advanced-glyph-color-did-update',
          ({ detail }) => (this.advancedColor.glyph = detail),
        );
        
        advColorUi.canvas = UiColorPicker(
          '#advanced-color-canvas',
          'advanced-canvas-color-did-update',
        );
        window.addEventListener(
          'advanced-canvas-color-did-update',
          ({ detail }) => (this.advancedColor.canvas = detail),
        );

        advColorUi.label = UiColorPicker(
          '#advanced-color-label',
          'advanced-label-color-did-update',
        );
        window.addEventListener(
          'advanced-label-color-did-update',
          ({ detail }) => (this.advancedColor.label = detail),
        );
      }

      $wire.on('icon-did-update', () => {
        if (this.useAdvancedColorUi) {
          return;
        }
        syncTailwindToAdvanced(this, advColorUi);
      });

      // initial sync -- defer to let pickr initialize
      setTimeout(() => syncTailwindToAdvanced(this, advColorUi));
    },
    label: this.$wire.entangle('label').defer,
    labelVisibility: this.$wire.entangle('labelVisibility').defer,
    labelTypeface: this.$wire.entangle('labelTypeface').defer,
    download() {
      downloadCurrent(this.label);
    },
    advancedColor: {
      glyph: '',
      canvas: '',
      label: '',
    },
    useAdvancedColorUi: this.$wire.entangle('useAdvancedColorUi'),
  };
}
