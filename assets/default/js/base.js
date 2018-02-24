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

window.onload = function () {

    /**
     * Retrieve an element up in the tree from its class name.
     */
    function getParentByClass(el, className) {
        var p = el.parentNode;
        if (p == null || p.classList.contains(className)) {
            return p;
        }
        return getParentByClass(p, className);
    }


    /**
     * Handle responsive menu.
     * Source: http://purecss.io/layouts/tucked-menu-vertical/
     */
    (function (window, document) {
        var menu = document.getElementById('shaarli-menu'),
            WINDOW_CHANGE_EVENT = ('onorientationchange' in window) ? 'orientationchange':'resize';

        function toggleHorizontal() {
            [].forEach.call(
                document.getElementById('shaarli-menu').querySelectorAll('.menu-transform'),
                function(el){
                    el.classList.toggle('pure-menu-horizontal');
                }
            );
        };

        function toggleMenu() {
            // set timeout so that the panel has a chance to roll up
            // before the menu switches states
            if (menu.classList.contains('open')) {
                setTimeout(toggleHorizontal, 500);
            }
            else {
                toggleHorizontal();
            }
            menu.classList.toggle('open');
            document.getElementById('menu-toggle').classList.toggle('x');
        };

        function closeMenu() {
            if (menu.classList.contains('open')) {
                toggleMenu();
            }
        }

        var menuToggle = document.getElementById('menu-toggle');
        if (menuToggle != null) {
            menuToggle.addEventListener('click', function (e) {
                toggleMenu();
            });
        }

        window.addEventListener(WINDOW_CHANGE_EVENT, closeMenu);
    })(this, this.document);

    /**
     * Fold/Expand shaares description and thumbnail.
     */
    var foldAllButtons = document.getElementsByClassName('fold-all');
    var foldButtons = document.getElementsByClassName('fold-button');

    [].forEach.call(foldButtons, function (foldButton) {
        // Retrieve description
        var description = null;
        var thumbnail = null;
        var linklistItem = getParentByClass(foldButton, 'linklist-item');
        if (linklistItem != null) {
            description = linklistItem.querySelector('.linklist-item-description');
            thumbnail = linklistItem.querySelector('.linklist-item-thumbnail');
            if (description != null || thumbnail != null) {
                foldButton.style.display = 'inline';
            }
        }

        foldButton.addEventListener('click', function (event) {
            event.preventDefault();
            toggleFold(event.target, description, thumbnail);
        });
    });

    if (foldAllButtons != null) {
        [].forEach.call(foldAllButtons, function (foldAllButton) {
            foldAllButton.addEventListener('click', function (event) {
                event.preventDefault();
                var state = foldAllButton.firstElementChild.getAttribute('class').indexOf('down') != -1 ? 'down' : 'up';
                [].forEach.call(foldButtons, function (foldButton) {
                    if (foldButton.firstElementChild.classList.contains('fa-chevron-up') && state == 'down'
                        || foldButton.firstElementChild.classList.contains('fa-chevron-down') && state == 'up'
                    ) {
                        return;
                    }
                    // Retrieve description
                    var description = null;
                    var thumbnail = null;
                    var linklistItem = getParentByClass(foldButton, 'linklist-item');
                    if (linklistItem != null) {
                        description = linklistItem.querySelector('.linklist-item-description');
                        thumbnail = linklistItem.querySelector('.linklist-item-thumbnail');
                        if (description != null || thumbnail != null) {
                            foldButton.style.display = 'inline';
                        }
                    }

                    toggleFold(foldButton.firstElementChild, description, thumbnail);
                });
                foldAllButton.firstElementChild.classList.toggle('fa-chevron-down');
                foldAllButton.firstElementChild.classList.toggle('fa-chevron-up');
                foldAllButton.title = state === 'down'
                    ? document.getElementById('translation-fold-all').innerHTML
                    : document.getElementById('translation-expand-all').innerHTML
            });
        });
    }

    function toggleFold(button, description, thumb)
    {
        // Switch fold/expand - up = fold
        if (button.classList.contains('fa-chevron-up')) {
            button.title = document.getElementById('translation-expand').innerHTML;
            if (description != null) {
                description.style.display = 'none';
            }
            if (thumb != null) {
                thumb.style.display = 'none';
            }
        }
        else {
            button.title = document.getElementById('translation-fold').innerHTML;
            if (description != null) {
                description.style.display = 'block';
            }
            if (thumb != null) {
                thumb.style.display = 'block';
            }
        }
        button.classList.toggle('fa-chevron-down');
        button.classList.toggle('fa-chevron-up');
    }

    /**
     * Confirmation message before deletion.
     */
    var deleteLinks = document.querySelectorAll('.confirm-delete');
    [].forEach.call(deleteLinks, function(deleteLink) {
        deleteLink.addEventListener('click', function(event) {
            if(! confirm(document.getElementById('translation-delete-link').innerHTML)) {
                event.preventDefault();
            }
        });
    });

    /**
     * Close alerts
     */
    var closeLinks = document.querySelectorAll('.pure-alert-close');
    [].forEach.call(closeLinks, function(closeLink) {
        closeLink.addEventListener('click', function(event) {
            var alert = getParentByClass(event.target, 'pure-alert-closable');
            alert.style.display = 'none';
        });
    });

    /**
     * New version dismiss.
     * Hide the message for one week using localStorage.
     */
    var newVersionDismiss = document.getElementById('new-version-dismiss');
    var newVersionMessage = document.querySelector('.new-version-message');
    if (newVersionMessage != null
        && localStorage.getItem('newVersionDismiss') != null
        && parseInt(localStorage.getItem('newVersionDismiss')) + 7*24*60*60*1000 > (new Date()).getTime()
    ) {
        newVersionMessage.style.display = 'none';
    }
    if (newVersionDismiss != null) {
        newVersionDismiss.addEventListener('click', function () {
            localStorage.setItem('newVersionDismiss', (new Date()).getTime());
        });
    }

    var hiddenReturnurl = document.getElementsByName('returnurl');
    if (hiddenReturnurl != null) {
        hiddenReturnurl.value = window.location.href;
    }

    /**
     * Autofocus text fields
     */
    var autofocusElements = document.querySelectorAll('.autofocus');
    var breakLoop = false;
    [].forEach.call(autofocusElements, function(autofocusElement) {
        if (autofocusElement.value == '' && ! breakLoop) {
            autofocusElement.focus();
            breakLoop = true;
        }
    });

    /**
     * Handle sub menus/forms
     */
    var openers = document.getElementsByClassName('subheader-opener');
    if (openers != null) {
        [].forEach.call(openers, function(opener) {
             opener.addEventListener('click', function(event) {
                 event.preventDefault();

                 var id = opener.getAttribute('data-open-id');
                 var sub = document.getElementById(id);

                 if (sub != null) {
                    [].forEach.call(document.getElementsByClassName('subheader-form'), function (element) {
                        if (element != sub) {
                            removeClass(element, 'open')
                        }
                     });

                     sub.classList.toggle('open');
                 }
             });
        });
    }

    function removeClass(element, classname) {
        element.className = element.className.replace(new RegExp('(?:^|\\s)'+ classname + '(?:\\s|$)'), ' ');
    }

    /**
     * Remove CSS target padding (for fixed bar)
     */
    if (location.hash != '') {
        var anchor = document.getElementById(location.hash.substr(1));
        if (anchor != null) {
            var padsize = anchor.clientHeight;
            this.window.scroll(0, this.window.scrollY - padsize);
            anchor.style.paddingTop = 0;
        }
    }

    /**
     * Text area resizer
     */
    var description = document.getElementById('lf_description');
    var observe = function (element, event, handler) {
        element.addEventListener(event, handler, false);
    };
    function init () {
        function resize () {
            /* Fix jumpy resizing: https://stackoverflow.com/a/18262927/1484919 */
            var scrollTop  = window.pageYOffset ||
                (document.documentElement || document.body.parentNode || document.body).scrollTop;

            description.style.height = 'auto';
            description.style.height = description.scrollHeight+10+'px';

            window.scrollTo(0, scrollTop);
        }
        /* 0-timeout to get the already changed text */
        function delayedResize () {
            window.setTimeout(resize, 0);
        }
        observe(description, 'change',  resize);
        observe(description, 'cut',     delayedResize);
        observe(description, 'paste',   delayedResize);
        observe(description, 'drop',    delayedResize);
        observe(description, 'keydown', delayedResize);

        resize();
    }

    if (description != null) {
        init();
        // Submit editlink form with CTRL + Enter in the text area.
        description.addEventListener('keydown', function (event) {
            if (event.ctrlKey && event.keyCode === 13) {
                document.getElementById('button-save-edit').click();
            }
        });
    }

    /**
     * Awesomplete trigger.
     */
    var tags = document.getElementById('lf_tags');
    if (tags != null) {
        awesompleteUniqueTag('#lf_tags');
    }

    /**
     * bLazy trigger
     */
    var picwall = document.getElementById('picwall_container');
    if (picwall != null) {
        var bLazy = new Blazy();
    }

    /**
     * Bookmarklet alert
     */
    var bookmarkletLinks = document.querySelectorAll('.bookmarklet-link');
    var bkmMessage = document.getElementById('bookmarklet-alert');
    [].forEach.call(bookmarkletLinks, function(link) {
        link.addEventListener('click', function(event) {
            event.preventDefault();
            alert(bkmMessage.value);
        });
    });

    /**
     * Firefox Social
     */
    var ffButton = document.getElementById('ff-social-button');
    if (ffButton != null) {
        ffButton.addEventListener('click', function(event) {
            activateFirefoxSocial(event.target);
        });
    }

    /**
     * Plugin admin order
     */
    var orderPA = document.querySelectorAll('.order');
    [].forEach.call(orderPA, function(link) {
        link.addEventListener('click', function(event) {
            event.preventDefault();
            if (event.target.classList.contains('order-up')) {
                return orderUp(event.target.parentNode.parentNode.getAttribute('data-order'));
            } else if (event.target.classList.contains('order-down')) {
                return orderDown(event.target.parentNode.parentNode.getAttribute('data-order'));
            }
        });
    });

    var continent = document.getElementById('continent');
    var city = document.getElementById('city');
    if (continent != null && city != null) {
        continent.addEventListener('change', function (event) {
            hideTimezoneCities(city, continent.options[continent.selectedIndex].value, true);
        });
        hideTimezoneCities(city, continent.options[continent.selectedIndex].value, false);
    }

    /**
     * Bulk actions
     */
    var linkCheckboxes = document.querySelectorAll('.delete-checkbox');
    var bar = document.getElementById('actions');
    [].forEach.call(linkCheckboxes, function(checkbox) {
        checkbox.style.display = 'inline-block';
        checkbox.addEventListener('click', function(event) {
            var count = 0;
            var linkCheckedCheckboxes = document.querySelectorAll('.delete-checkbox:checked');
            [].forEach.call(linkCheckedCheckboxes, function(checkbox) {
                count++;
            });
            if (count == 0 && bar.classList.contains('open')) {
                bar.classList.toggle('open');
            } else if (count > 0 && ! bar.classList.contains('open')) {
                bar.classList.toggle('open');
            }
        });
    });

    var deleteButton = document.getElementById('actions-delete');
    var token = document.querySelector('input[type="hidden"][name="token"]');
    if (deleteButton != null && token != null) {
        deleteButton.addEventListener('click', function(event) {
            event.preventDefault();

            var links = [];
            var linkCheckedCheckboxes = document.querySelectorAll('.delete-checkbox:checked');
            [].forEach.call(linkCheckedCheckboxes, function(checkbox) {
                links.push({
                    'id': checkbox.value,
                    'title': document.querySelector('.linklist-item[data-id="'+ checkbox.value +'"] .linklist-link').innerHTML
                });
            });

            var message = 'Are you sure you want to delete '+ links.length +' links?\n';
            message += 'This action is IRREVERSIBLE!\n\nTitles:\n';
            var ids = [];
            links.forEach(function(item) {
                message += '  - '+ item['title'] +'\n';
                ids.push(item['id']);
            });

            if (window.confirm(message)) {
                window.location = '?delete_link&lf_linkdate='+ ids.join('+') +'&token='+ token.value;
            }
        });
    }

    /**
     * Tag list operations
     *
     * TODO: support error code in the backend for AJAX requests
     */
    var tagList = document.querySelector('input[name="taglist"]');
    var existingTags = tagList ? tagList.value.split(' ') : [];
    var awesomepletes = [];

    // Display/Hide rename form
    var renameTagButtons = document.querySelectorAll('.rename-tag');
    [].forEach.call(renameTagButtons, function(rename) {
        rename.addEventListener('click', function(event) {
            event.preventDefault();
            var block = findParent(event.target, 'div', {'class': 'tag-list-item'});
            var form = block.querySelector('.rename-tag-form');
            if (form.style.display == 'none' || form.style.display == '') {
                form.style.display = 'block';
            } else {
                form.style.display = 'none';
            }
            block.querySelector('input').focus();
        });
    });

    // Rename a tag with an AJAX request
    var renameTagSubmits = document.querySelectorAll('.validate-rename-tag');
    [].forEach.call(renameTagSubmits, function(rename) {
        rename.addEventListener('click', function(event) {
            event.preventDefault();
            var block = findParent(event.target, 'div', {'class': 'tag-list-item'});
            var input = block.querySelector('.rename-tag-input');
            var totag = input.value.replace('/"/g', '\\"');
            if (totag.trim() == '') {
                return;
            }
            var fromtag = block.getAttribute('data-tag');
            var token = document.getElementById('token').value;

            xhr = new XMLHttpRequest();
            xhr.open('POST', '?do=changetag');
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status !== 200) {
                    alert('An error occurred. Return code: '+ xhr.status);
                    location.reload();
                } else {
                    block.setAttribute('data-tag', totag);
                    input.setAttribute('name', totag);
                    input.setAttribute('value', totag);
                    findParent(input, 'div', {'class': 'rename-tag-form'}).style.display = 'none';
                    block.querySelector('a.tag-link').innerHTML = htmlEntities(totag);
                    block.querySelector('a.tag-link').setAttribute('href', '?searchtags='+ encodeURIComponent(totag));
                    block.querySelector('a.rename-tag').setAttribute('href', '?do=changetag&fromtag='+ encodeURIComponent(totag));

                    // Refresh awesomplete values
                    for (var key in existingTags) {
                        if (existingTags[key] == fromtag) {
                            existingTags[key] = totag;
                        }
                    }
                    awesomepletes = updateAwesompleteList('.rename-tag-input', existingTags, awesomepletes);
                }
            };
            xhr.send('renametag=1&fromtag='+ encodeURIComponent(fromtag) +'&totag='+ encodeURIComponent(totag) +'&token='+ token);
            refreshToken();
        });
    });

    // Validate input with enter key
    var renameTagInputs = document.querySelectorAll('.rename-tag-input');
    [].forEach.call(renameTagInputs, function(rename) {

        rename.addEventListener('keypress', function(event) {
            if (event.keyCode === 13) { // enter
                findParent(event.target, 'div', {'class': 'tag-list-item'}).querySelector('.validate-rename-tag').click();
            }
        });
    });

    // Delete a tag with an AJAX query (alert popup confirmation)
    var deleteTagButtons = document.querySelectorAll('.delete-tag');
    [].forEach.call(deleteTagButtons, function(rename) {
        rename.style.display = 'inline';
        rename.addEventListener('click', function(event) {
            event.preventDefault();
            var block = findParent(event.target, 'div', {'class': 'tag-list-item'});
            var tag = block.getAttribute('data-tag');
            var token = document.getElementById('token').value;

            if (confirm('Are you sure you want to delete the tag "'+ tag +'"?')) {
                xhr = new XMLHttpRequest();
                xhr.open('POST', '?do=changetag');
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    block.remove();
                };
                xhr.send(encodeURI('deletetag=1&fromtag='+ tag +'&token='+ token));
                refreshToken();
            }
        });
    });

    updateAwesompleteList('.rename-tag-input', existingTags, awesomepletes);
};

/**
 * Find a parent element according to its tag and its attributes
 *
 * @param element    Element where to start the search
 * @param tagName    Expected parent tag name
 * @param attributes Associative array of expected attributes (name=>value).
 *
 * @returns Found element or null.
 */
function findParent(element, tagName, attributes)
{
    while (element) {
        if (element.tagName.toLowerCase() == tagName) {
            var match = true;
            for (var key in attributes) {
                if (! element.hasAttribute(key)
                    || (attributes[key] != '' && element.getAttribute(key).indexOf(attributes[key]) == -1)
                ) {
                    match = false;
                    break;
                }
            }

            if (match) {
                return element;
            }
        }
        element = element.parentElement;
    }
    return null;
}

/**
 * Ajax request to refresh the CSRF token.
 */
function refreshToken()
{
    var xhr = new XMLHttpRequest();
    xhr.open('GET', '?do=token');
    xhr.onload = function() {
        var token = document.getElementById('token');
        token.setAttribute('value', xhr.responseText);
    };
    xhr.send();
}

/**
 * Update awesomplete list of tag for all elements matching the given selector
 *
 * @param selector  CSS selector
 * @param tags      Array of tags
 * @param instances List of existing awesomplete instances
 */
function updateAwesompleteList(selector, tags, instances)
{
    // First load: create Awesomplete instances
    if (instances.length == 0) {
        var elements = document.querySelectorAll(selector);
        [].forEach.call(elements, function (element) {
            instances.push(new Awesomplete(
                element,
                {'list': tags}
            ));
        });
    } else {
        // Update awesomplete tag list
        for (var key in instances) {
            instances[key].list = tags;
        }
    }
    return instances;
}

/**
 * html_entities in JS
 *
 * @see http://stackoverflow.com/questions/18749591/encode-html-entities-in-javascript
 */
function htmlEntities(str)
{
    return str.replace(/[\u00A0-\u9999<>\&]/gim, function(i) {
        return '&#'+i.charCodeAt(0)+';';
    });
}

function activateFirefoxSocial(node) {
    var loc = location.href;
    var baseURL = loc.substring(0, loc.lastIndexOf("/") + 1);
    var title = document.title;

    // Keeping the data separated (ie. not in the DOM) so that it's maintainable and diffable.
    var data = {
        name: title,
        description: document.getElementById('translation-delete-link').innerHTML,
        author: "Shaarli",
        version: "1.0.0",

        iconURL: baseURL + "/images/favicon.ico",
        icon32URL: baseURL + "/images/favicon.ico",
        icon64URL: baseURL + "/images/favicon.ico",

        shareURL: baseURL + "?post=%{url}&title=%{title}&description=%{text}&source=firefoxsocialapi",
        homepageURL: baseURL
    };
    node.setAttribute("data-service", JSON.stringify(data));

    var activate = new CustomEvent("ActivateSocialFeature");
    node.dispatchEvent(activate);
}

/**
 * Add the class 'hidden' to city options not attached to the current selected continent.
 *
 * @param cities           List of <option> elements
 * @param currentContinent Current selected continent
 * @param reset            Set to true to reset the selected value
 */
function hideTimezoneCities(cities, currentContinent) {
    var first = true;
    if (reset == null) {
        reset = false;
    }
    [].forEach.call(cities, function (option) {
        if (option.getAttribute('data-continent') != currentContinent) {
            option.className = 'hidden';
        } else {
            option.className = '';
            if (reset === true && first === true) {
                option.setAttribute('selected', 'selected');
                first = false;
            }
        }
    });
}
