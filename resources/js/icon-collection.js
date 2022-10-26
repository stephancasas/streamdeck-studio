export default function () {
  let $refs, $watch, $wire;

  const ADJECTIVES = [
    'Cool',
    'Awesome',
    'Amazing',
    'Terrific',
    'Incredible',
    'Impressive',
    'Neat',
    'Groovy',
    'Phenomenal',
    'Brilliant',
    'Super',
    'Outstanding',
  ];

  /**
   * Create a collection cell.
   * @returns {Element}
   */
  const cellTemplate = () => {
    const html = document.querySelector('template#grid-render-template').content
      .children[0].outerHTML;
    const d = document.createElement('div');
    d.innerHTML = html;
    return d.firstChild;
  };

  /**
   * Generate a non-compliant UUID.
   * @returns {string}
   */
  const uuid = () =>
    ([1e7] + -1e3 + -4e3 + -8e3 + -1e11).replace(/[018]/g, (a) =>
      (a ^ ((Math.random() * 16) >> (a / 4))).toString(16),
    );

  /**
   * Create a new prefixed key for storage.
   * @param  {...any} str The key series to prefix.
   * @returns {string}
   */
  const storageKey = (...str) => `${STORAGE_KEY_PREFIX}${str.join('.')}`;
  const STORAGE_KEY_PREFIX = 'studio.streamdeck.';

  /**
   * Store the icon in the collection and localStorage.
   */
  const collectIcon = (detail) => {
    const gridCell = cellTemplate();
    gridCell.removeAttribute('style');
    gridCell.style.transform = 'scale(0)';

    // image src from existing png
    const image = document.querySelector(`#png-preview`).getAttribute('src');

    const img = document.createElement('img');
    img.src = image;
    img.style = 'width: 100%;';

    const iconId = uuid();
    gridCell.setAttribute('data--icon-id', iconId);
    gridCell.querySelector('.grid-preview-target').appendChild(img);

    $refs.collectionGrid.appendChild(gridCell);
    setTimeout(() => (gridCell.style.transform = 'scale(1)'));

    // store the png for recall on page reload
    window.localStorage.setItem(
      storageKey('icon', iconId),
      JSON.stringify({ image, detail }),
    );
  };

  /**
   * Push an existing icon's details into the editor.
   * @param {str} iconId The stored icon id.
   */
  const loadIconInEditor = (iconId) => {
    const { detail } = JSON.parse(
      window.localStorage.getItem(storageKey('icon', iconId)),
    );

    window.Livewire.emit('load-icon-from-storage', detail);
  };

  /**
   * Delete a stored icon.
   * @param {Element} iconEl The icon element to remove
   */
  const deleteIcon = (iconEl) => {
    window.localStorage.removeItem(
      storageKey('icon', iconEl.getAttribute('data--icon-id')),
    );

    iconEl.style.transform = 'scale(0)';
    setTimeout(() => iconEl.remove(), 250);
  };

  let collectionName =
    window.localStorage.getItem(storageKey('collection_name')) || '';
  const downloadCollection = () => {
    const icons = getIconKeysFromStorage().map((key) =>
      JSON.parse(window.localStorage.getItem(key)),
    );

    $wire.call(
      'telemetry',
      collectionName,
      icons.map(({ detail }) => detail),
    );

    const zip = new JSZip();
    icons.forEach(({ image, detail: { label } }) => {
      zip.file(
        `${label.replace(/\s/g, '-').replace(/\./g, '')}.png`,
        image.match(/,.*/)[0],
        { base64: true },
      );
    });

    zip.generateAsync({ type: 'blob' }).then((blob) => {
      const a = document.createElement('a');
      a.href = URL.createObjectURL(blob);
      a.download = `${(collectionName || 'streamdeck-studio-icons')
        .replace(/\s/g, '-')
        .replace(/\./g, '')}.zip`;
      a.click();
    });
  };

  let debounce;
  const handleCollectionNameChange = () => {
    clearTimeout(debounce);
    debounce = setTimeout(() => {
      window.localStorage.setItem(
        storageKey('collection_name'),
        collectionName,
      );
    }, 500);
  };

  /**
   * Load an icon into the collection element from storage.
   * @param {string} key The storage key for the icon to load.
   */
  const loadIconInCollectionFromStorage = (key) => {
    const { image } = JSON.parse(window.localStorage.getItem(key));
    const iconId = key.replace(`${storageKey('icon')}.`, '');

    const img = document.createElement('img');
    img.src = image;
    img.style = 'width: 100%;';

    const gridCell = cellTemplate();
    gridCell.setAttribute('data--icon-id', iconId);
    gridCell.querySelector('.grid-preview-target').appendChild(img);

    $refs.collectionGrid.appendChild(gridCell);
  };

  const getIconKeysFromStorage = () =>
    Object.keys(window.localStorage).filter(
      (key) => !!key.match(new RegExp(`^${storageKey('icon')}.*`)),
    );

  /**
   * On page load, populate the icon collection from storage.
   */
  const initializeCollection = () => {
    getIconKeysFromStorage().forEach(loadIconInCollectionFromStorage);
  };

  return {
    init() {
      $refs = this.$refs;
      $wire = this.$wire;
      $watch = this.$watch;
      initializeCollection();

      setInterval(
        () =>
          (this.placeholder =
            ADJECTIVES[
              ADJECTIVES.findIndex((adj) => adj === this.placeholder) + 1
            ] || ADJECTIVES[0]),
        6000,
      );
    },
    collectEditorIcon: collectIcon,
    loadIconInEditor,
    deleteIcon,
    downloadCollection,
    set collectionName(value) {
      collectionName = value;
      handleCollectionNameChange(); // i'm feeling lazy
    },
    get collectionName() {
      return collectionName;
    },
    placeholder: ADJECTIVES[0],
  };
}
