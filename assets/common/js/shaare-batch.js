const sendBookmarkForm = (basePath, formElement) => {
  const inputs = formElement
    .querySelectorAll('input[type="text"], textarea, input[type="checkbox"], input[type="hidden"]');

  const formData = new FormData();
  [...inputs].forEach((input) => {
    formData.append(input.getAttribute('name'), input.value);
  });

  return new Promise((resolve, reject) => {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', `${basePath}/admin/shaare`);
    xhr.onload = () => {
      if (xhr.status !== 200) {
        alert(`An error occurred. Return code: ${xhr.status}`);
        reject();
      } else {
        formElement.remove();
        resolve();
      }
    };
    xhr.send(formData);
  });
};

const sendBookmarkDelete = (buttonElement, formElement) => (
  new Promise((resolve, reject) => {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', buttonElement.href);
    xhr.onload = () => {
      if (xhr.status !== 200) {
        alert(`An error occurred. Return code: ${xhr.status}`);
        reject();
      } else {
        formElement.remove();
        resolve();
      }
    };
    xhr.send();
  })
);

const redirectIfEmptyBatch = (basePath, formElements, path) => {
  if (formElements == null || formElements.length === 0) {
    window.location.href = `${basePath}${path}`;
  }
};

(() => {
  const basePath = document.querySelector('input[name="js_base_path"]').value;
  const getForms = () => document.querySelectorAll('form[name="linkform"]');

  const cancelButtons = document.querySelectorAll('[name="cancel-batch-link"]');
  if (cancelButtons != null) {
    [...cancelButtons].forEach((cancelButton) => {
      cancelButton.addEventListener('click', (e) => {
        e.preventDefault();
        e.target.closest('form[name="linkform"]').remove();
        redirectIfEmptyBatch(basePath, getForms(), '/admin/add-shaare');
      });
    });
  }

  const saveButtons = document.querySelectorAll('[name="save_edit"]');
  if (saveButtons != null) {
    [...saveButtons].forEach((saveButton) => {
      saveButton.addEventListener('click', (e) => {
        e.preventDefault();

        const formElement = e.target.closest('form[name="linkform"]');
        sendBookmarkForm(basePath, formElement)
          .then(() => redirectIfEmptyBatch(basePath, getForms(), '/'));
      });
    });
  }

  const saveAllButtons = document.querySelectorAll('[name="save_edit_batch"]');
  if (saveAllButtons != null) {
    [...saveAllButtons].forEach((saveAllButton) => {
      saveAllButton.addEventListener('click', (e) => {
        e.preventDefault();

        const promises = [];
        [...getForms()].forEach((formElement) => {
          promises.push(sendBookmarkForm(basePath, formElement));
        });

        Promise.all(promises).then(() => {
          window.location.href = basePath || '/';
        });
      });
    });
  }

  const deleteButtons = document.querySelectorAll('[name="delete_link"]');
  if (deleteButtons != null) {
    [...deleteButtons].forEach((deleteButton) => {
      deleteButton.addEventListener('click', (e) => {
        e.preventDefault();

        const formElement = e.target.closest('form[name="linkform"]');
        sendBookmarkDelete(e.target, formElement)
          .then(() => redirectIfEmptyBatch(basePath, getForms(), '/'));
      });
    });
  }
})();
