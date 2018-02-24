import Awesomplete from 'awesomplete';
import 'awesomplete/awesomplete.css';

(() => {
  const awp = Awesomplete.$;
  const autocompleteFields = document.querySelectorAll('input[data-multiple]');
  [...autocompleteFields].forEach((autocompleteField) => {
    const awesomplete = new Awesomplete(awp(autocompleteField));
    awesomplete.filter = (text, input) => Awesomplete.FILTER_CONTAINS(text, input.match(/[^ ]*$/)[0]);
    awesomplete.replace = (text) => {
      const before = awesomplete.input.value.match(/^.+ \s*|/)[0];
      awesomplete.input.value = `${before}${text} `;
    };
    awesomplete.minChars = 1;

    autocompleteField.addEventListener('input', () => {
      const proposedTags = autocompleteField.getAttribute('data-list').replace(/,/g, '').split(' ');
      const reg = /(\w+) /g;
      let match;
      while ((match = reg.exec(autocompleteField.value)) !== null) {
        const id = proposedTags.indexOf(match[1]);
        if (id !== -1) {
          proposedTags.splice(id, 1);
        }
      }

      awesomplete.list = proposedTags;
    });
  });
})();
