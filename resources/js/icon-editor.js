import Html2Canvas from 'html2canvas';

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
    },
    label: this.$wire.entangle('label').defer,
    labelVisibility: this.$wire.entangle('labelVisibility').defer,
    labelTypeface: this.$wire.entangle('labelTypeface').defer,
    download() {
      downloadCurrent(this.label);
    },
  };
}
