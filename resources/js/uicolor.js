export default {
  resolveTailwindColor: (cls, toHex = true) => {
    const div = document.createElement('div');
    div.classList.add(cls);
    document.body.appendChild(div);

    const prop = { text: 'color', bg: 'background-color', unknown: null }[
      cls.split('-')[0] || 'unknown'
    ];

    if (!prop) {
      console.warn(
        `Invalid class given to resolveTailwindColor. Should be of type 'text' or 'bg'`,
      );
      return '#000';
    }

    const rgb = `${getComputedStyle(div).getPropertyValue(prop)}`;
    div.remove();

    return toHex
      ? `#${rgb
          .match(/\d+/g)
          .map((x) => `0${parseInt(x).toString(16)}`.match(/..$/)[0])
          .join('')}`
      : rgb;
  },
};
