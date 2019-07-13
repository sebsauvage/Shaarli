## Plugin installation

There is a bunch of plugins shipped with Shaarli, where there is nothing to do to install them.

If you want to install a third party plugin:

- Download it.
- Put it in the `plugins` directory in Shaarli's installation folder.
- Make sure you put it correctly:

```
| index.php
| plugins/
|---| custom_plugin/
|   |---| custom_plugin.php
|   |---| ...

```

  * Make sure your webserver can read and write the files in your plugin folder.

## Plugin configuration

In Shaarli's administration page (`Tools` link), go to `Plugin administration`.

Here you can enable and disable all plugins available, and configure them.

![administration screenshot](https://camo.githubusercontent.com/5da68e191969007492ca0fbeb25f3b2357b748cc/687474703a2f2f692e696d6775722e636f6d2f766837544643712e706e67)

## Plugin order

In the plugin administration page, you can move enabled plugins to the top or bottom of the list. The first plugins in the list will be processed first.

This is important in case plugins are depending on each other. Read plugins README details for more information.

**Use case**: The (non existent) plugin `shaares_footer` adds a footer to every shaare in Markdown syntax. It needs to be processed *before* (higher in the list) the Markdown plugin. Otherwise its syntax won't be translated in HTML.

## File mode

Enabled plugin are stored in your `config.json.php` parameters file, under the `array`:

```php
$GLOBALS['config']['ENABLED_PLUGINS']
```

You can edit them manually here.
Example:

```php
$GLOBALS['config']['ENABLED_PLUGINS'] = array(
    'qrcode',
    'archiveorg',
    'wallabag',
    'markdown',
);
```

### Plugin usage

#### Official plugins

Usage of each plugin is documented in it's README file:

 * `addlink-toolbar`: Adds the addlink input on the linklist page
 * `archiveorg`: For each link, add an Archive.org icon
 * `default_colors`: Override default theme colors.
 * `isso`: Let visitor comment your shaares on permalinks with Isso.
 * [`markdown`](https://github.com/shaarli/Shaarli/blob/master/plugins/markdown/README.md): Render shaare description with Markdown syntax.
 * `piwik`: A plugin that adds Piwik tracking code to Shaarli pages.
 * [`playvideos`](https://github.com/shaarli/Shaarli/blob/master/plugins/playvideos/README.md): Add a button in the toolbar allowing to watch all videos.
 * `pubsubhubbub`: Enable PubSubHubbub feed publishing
 * `qrcode`: For each link, add a QRCode icon.
 * [`wallabag`](https://github.com/shaarli/Shaarli/blob/master/plugins/wallabag/README.md):  For each link, add a Wallabag icon to save it in your instance.



#### Third party plugins

See [Community & related software](https://shaarli.readthedocs.io/en/master/Community-&-Related-software/)
