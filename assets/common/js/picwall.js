import Blazy from 'blazy';

(() => {
  const picwall = document.getElementById('picwall_container');
  if (picwall != null) {
    // Suppress ESLint error because that's how bLazy works
    /* eslint-disable no-new */
    new Blazy();
  }
})();
