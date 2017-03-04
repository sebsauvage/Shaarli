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
     |--- README.md
```

To enable the plugin, just check it in the plugin administration page.

You can also add `markdown` to your list of enabled plugins in `data/config.json.php`
(`general.enabled_plugins` list).

This should look like:

```
"general": {
  "enabled_plugins": [
    "markdown",
    [...]
  ],
}
```

Parsedown parsing library is imported using Composer. If you installed Shaarli using `git`,
or the `master` branch, run

    composer update --no-dev --prefer-dist

### No Markdown tag

If the tag `nomarkdown` is set for a shaare, it won't be converted to Markdown syntax.
 
> Note: this is a special tag, so it won't be displayed in link list.

### HTML escape

By default, HTML tags are escaped. You can enable HTML tags rendering
by setting `security.markdwon_escape` to `false` in `data/config.json.php`:

```json
{
  "security": {
    "markdown_escape": false
  }
}
```

With this setting, Markdown support HTML tags. For example:

    > <strong>strong</strong><strike>strike</strike>
   
Will render as:

> <strong>strong</strong><strike>strike</strike>


**Warning:**

  * This setting might present **security risks** (XSS) on shared instances, even though tags 
  such as script, iframe, etc should be disabled.
  * If you want to shaare HTML code, it is necessary to use inline code or code blocks.
  * If your shaared descriptions contained HTML tags before enabling the markdown plugin, 
enabling it might break your page.

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
