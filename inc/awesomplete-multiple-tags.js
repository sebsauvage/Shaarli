$ = Awesomplete.$;
awesomplete = new Awesomplete($('input[data-multiple]'), {
    filter: function(text, input) {
        return Awesomplete.FILTER_CONTAINS(text, input.match(/[^ ]*$/)[0]);
    },
    replace: function(text) {
        var before = this.input.value.match(/^.+ \s*|/)[0];
        this.input.value = before + text + " ";
    },
    minChars: 1
});

/**
 * Remove already selected items from autocompletion list.
 * HTML list is never updated, so removing a tag will add it back to awesomplete.
 *
 * FIXME: This a workaround waiting for awesomplete to handle this.
 *  https://github.com/LeaVerou/awesomplete/issues/16749
 */
function awesompleteUniqueTag(selector) {
    var input = document.querySelector(selector);
    input.addEventListener('input', function()
    {
        proposedTags = input.getAttribute('data-list').replace(/,/g, '').split(' ');
        reg = /(\w+) /g;
        while((match = reg.exec(input.value)) !== null) {
            id = proposedTags.indexOf(match[1]);
            if(id != -1 ) {
                proposedTags.splice(id, 1);
            }
        }

        awesomplete.list = proposedTags;
    });
}
