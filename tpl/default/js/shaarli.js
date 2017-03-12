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
            });
        });
    }

    function toggleFold(button, description, thumb)
    {
        // Switch fold/expand - up = fold
        if (button.classList.contains('fa-chevron-up')) {
            button.title = 'Expand';
            if (description != null) {
                description.style.display = 'none';
            }
            if (thumb != null) {
                thumb.style.display = 'none';
            }
        }
        else {
            button.title = 'Fold';
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
            if(! confirm('Are you sure you want to delete this link ?')) {
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
    // ES6 syntax
    let autofocusElements = document.querySelectorAll('.autofocus');
    for (let autofocusElement of autofocusElements) {
        if (autofocusElement.value == '') {
            autofocusElement.focus();
            break;
        }
    }

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
            description.style.height = 'auto';
            description.style.height = description.scrollHeight+10+'px';
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
     *
     * Note: Requires a modern browser.
     */
    if (testEs6Compatibility()) {
        let linkCheckboxes = document.querySelectorAll('.delete-checkbox');
        for(let checkbox of linkCheckboxes) {
            checkbox.style.display = 'block';
            checkbox.addEventListener('click', function(event) {
                let count = 0;
                for(let checkbox of linkCheckboxes) {
                    count = checkbox.checked ? count + 1 : count;
                }
                let bar = document.getElementById('actions');
                if (count == 0 && bar.classList.contains('open')) {
                    bar.classList.toggle('open');
                } else if (count > 0 && ! bar.classList.contains('open')) {
                    bar.classList.toggle('open');
                }
            });
        }

        let deleteButton = document.getElementById('actions-delete');
        let token = document.querySelector('input[type="hidden"][name="token"]');
        if (deleteButton != null && token != null) {
            deleteButton.addEventListener('click', function(event) {
                event.preventDefault();

                let links = [];
                for(let checkbox of linkCheckboxes) {
                    if (checkbox.checked) {
                        links.push({
                            'id': checkbox.value,
                            'title': document.querySelector('.linklist-item[data-id="'+ checkbox.value +'"] .linklist-link').innerHTML
                        });
                    }
                }

                let message = 'Are you sure you want to delete '+ links.length +' links?\n';
                message += 'This action is IRREVERSIBLE!\n\nTitles:\n';
                let ids = '';
                for (let item of links) {
                    message += '  - '+ item['title'] +'\n';
                    ids += item['id'] +'+';
                }
                if (window.confirm(message)) {
                    window.location = '?delete_link&lf_linkdate='+ ids +'&token='+ token.value;
                }
            });
        }
    }
};

function activateFirefoxSocial(node) {
    var loc = location.href;
    var baseURL = loc.substring(0, loc.lastIndexOf("/"));

    // Keeping the data separated (ie. not in the DOM) so that it's maintainable and diffable.
    var data = {
        name: "{$shaarlititle}",
        description: "The personal, minimalist, super-fast, database free, bookmarking service by the Shaarli community.",
        author: "Shaarli",
        version: "1.0.0",

        iconURL: baseURL + "/images/favicon.ico",
        icon32URL: baseURL + "/images/favicon.ico",
        icon64URL: baseURL + "/images/favicon.ico",

        shareURL: baseURL + "{noparse}?post=%{url}&title=%{title}&description=%{text}&source=firefoxsocialapi{/noparse}",
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
function hideTimezoneCities(cities, currentContinent, reset = false) {
    var first = true;
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

/**
 * Check if the browser is compatible with ECMAScript 6 syntax
 *
 * Source: http://stackoverflow.com/a/29046739/1484919
 *
 * @returns {boolean}
 */
function testEs6Compatibility()
{
    "use strict";

    try { eval("var foo = (x)=>x+1"); }
    catch (e) { return false; }
    return true;
}
