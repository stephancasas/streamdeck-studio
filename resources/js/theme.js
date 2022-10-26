export default function () {
  const resolveTheme = () =>
    [...document.documentElement.classList].includes('dark') ? 'dark' : 'light';
  return {
    toggle() {
      console.log(this.theme);
      const method =
        this.theme === 'dark' ? ['remove', 'light'] : ['add', 'dark'];
      document.documentElement.classList[method[0]]('dark');
      window.localStorage.setItem('theme', method[1]);
      this.theme = method[1];
    },
    theme: resolveTheme(),
  };
}
