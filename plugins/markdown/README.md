## Markdown Shaarli plugin

Convert all your shaares description to HTML formatted Markdown.

Read more about Markdown syntax here.

### Installation

Clone this repository inside your `tpl/plugins/` directory, or download the archive and unpack it there.
The directory structure should look like:

```
??? plugins
    ??? markdown
        ??? help.html
        ??? markdown.css
        ??? markdown.meta
        ??? markdown.php
        ??? Parsedown.php
        ??? README.md
```

To enable the plugin, add `markdown` to your list of enabled plugins in `data/config.php`
(`ENABLED_PLUGINS` array).

This should look like:

```
$GLOBALS['config']['ENABLED_PLUGINS'] = array('qrcode', 'any_other_plugin', 'markdown')
```

### Known issue

#### Redirector

If you're using a redirector, you *need* to add a space after a link,
otherwise the rest of the line will be `urlencode`.

```
[link](http://domain.tld)-->test
```

Will consider `http://domain.tld)-->test` as URL.

Instead, add an additional space.

```
[link](http://domain.tld) -->test
```

> Won't fix because a `)` is a valid part of an URL.
