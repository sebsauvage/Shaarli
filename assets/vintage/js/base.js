import Awesomplete from 'awesomplete';
import 'awesomplete/awesomplete.css';

(() => {
  const autocompleteFields = document.querySelectorAll('input[data-multiple]');
  const tagsSeparatorElement = document.querySelector('input[name="tags_separator"]');
  const tagsSeparator = tagsSeparatorElement ? tagsSeparatorElement.value || " " : " ";

  [...autocompleteFields].forEach((autocompleteField) => {
    const awesome = new Awesomplete(Awesomplete.$(autocompleteField));

    // Tags are separated by separator
    awesome.filter = (text, input) => Awesomplete.FILTER_CONTAINS(
      text,
      input.match(new RegExp(`[^${tagsSeparator}]*$`))[0])
    ;
    // Insert new selected tag in the input
    awesome.replace = (text) => {
      const before = awesome.input.value.match(new RegExp(`^.+${tagsSeparator}+|`))[0];
      awesome.input.value = `${before}${text}${tagsSeparator}`;
    };
    // Highlight found items
    awesome.item = (text, input) => Awesomplete.ITEM(text, input.match(new RegExp(`[^${tagsSeparator}]*$`))[0]);
    // Don't display already selected items
    const reg = new RegExp(`/(\w+)${tagsSeparator}/g`);
    let match;
    awesome.data = (item, input) => {
      while ((match = reg.exec(input))) {
        if (item === match[1]) {
          return '';
        }
      }
      return item;
    };
    awesome.minChars = 1;
  });
})();
