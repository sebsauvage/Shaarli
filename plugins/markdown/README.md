## Markdown Shaarli plugin

Convert all your shaares description to HTML formatted Markdown.

[Read more about Markdown syntax](http://daringfireball.net/projects/markdown/syntax).

Markdown processing is done with [Parsedown library](https://github.com/erusev/parsedown).

### Installation

As a default plugin, it should already be in `tpl/plugins/` directory.
If not, download and unpack it there.

The directory structure should look like:

```
--- plugins
  |--- markdown
     |--- help.html
     |--- markdown.css
     |--- markdown.meta
     |--- markdown.php
     |--- Parsedown.php
     |--- README.md
```

To enable the plugin, just check it in the plugin administration page.

You can also add `markdown` to your list of enabled plugins in `data/config.php`
(`ENABLED_PLUGINS` array).

This should look like:

```
$GLOBALS['config']['ENABLED_PLUGINS'] = array('qrcode', 'any_other_plugin', 'markdown')
```

### No Markdown tag

If the tag `.nomarkdown` is set for a shaare, it won't be converted to Markdown syntax.
 
> Note: it's a private tag (leading dot), so it won't be displayed to visitors.

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
