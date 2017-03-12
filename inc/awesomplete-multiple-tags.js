/** @licstart  The following is the entire license notice for the
 *  JavaScript code in this page.
 *
 *   Copyright: (c) 2011-2015 Sébastien SAUVAGE <sebsauvage@sebsauvage.net>
 *              (c) 2011-2017 The Shaarli Community, see AUTHORS
 *
 *   This software is provided 'as-is', without any express or implied warranty.
 *   In no event will the authors be held liable for any damages arising from
 *   the use of this software.
 *
 *   Permission is granted to anyone to use this software for any purpose,
 *   including commercial applications, and to alter it and redistribute it
 *   freely, subject to the following restrictions:
 *
 *   1. The origin of this software must not be misrepresented; you must not
 *   claim that you wrote the original software. If you use this software
 *   in a product, an acknowledgment in the product documentation would
 *   be appreciated but is not required.
 *
 *   2. Altered source versions must be plainly marked as such, and must
 *   not be misrepresented as being the original software.
 *
 *   3. This notice may not be removed or altered from any source distribution.
 *
 *  @licend  The above is the entire license notice
 *  for the JavaScript code in this page.
 */

var awp = Awesomplete.$;
var autocompleteFields = document.querySelectorAll('input[data-multiple]');
[].forEach.call(autocompleteFields, function(autocompleteField) {
    awesomplete = new Awesomplete(awp(autocompleteField), {
        filter: function (text, input) {
            return Awesomplete.FILTER_CONTAINS(text, input.match(/[^ ]*$/)[0]);
        },
        replace: function (text) {
            var before = this.input.value.match(/^.+ \s*|/)[0];
            this.input.value = before + text + " ";
        },
        minChars: 1
    })
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
