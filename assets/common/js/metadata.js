import he from 'he';

function clearLoaders(loaders) {
  if (loaders != null && loaders.length > 0) {
    [...loaders].forEach((loader) => {
      loader.classList.remove('loading-input');
    });
  }
}

(() => {
  const loaders = document.querySelectorAll('.loading-input');
  const inputTitle = document.querySelector('input[name="lf_title"]');
  if (inputTitle != null && inputTitle.value.length > 0) {
    clearLoaders(loaders);
    return;
  }

  const url = document.querySelector('input[name="lf_url"]').value;
  const basePath = document.querySelector('input[name="js_base_path"]').value;

  const xhr = new XMLHttpRequest();
  xhr.open('GET', `${basePath}/admin/metadata?url=${encodeURI(url)}`, true);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  xhr.onload = () => {
    const result = JSON.parse(xhr.response);
    Object.keys(result).forEach((key) => {
      if (result[key] !== null && result[key].length) {
        const element = document.querySelector(`input[name="lf_${key}"], textarea[name="lf_${key}"]`);
        if (element != null && element.value.length === 0) {
          element.value = he.decode(result[key]);
        }
      }
    });
    clearLoaders(loaders);
  };

  xhr.send();
})();
