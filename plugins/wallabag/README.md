## Save to Wallabag plugin for Shaarli

For each link in your Shaarli, adds a button to save the target page in your [wallabag](https://www.wallabag.org/).

### Installation

Clone this repository inside your `tpl/plugins/` directory, or download the archive and unpack it there.  
The directory structure should look like:

```
└── tpl
    └── plugins
        └── wallabag
            ├── README.md
            ├── config.php.dist
            ├── wallabag.html
            ├── wallabag.php
            └── wallabag.png
```

To enable the plugin, add `'wallabag'` to your list of enabled plugins in `data/options.php` (`PLUGINS` array).
This should look like:

```
$GLOBALS['config']['PLUGINS'] = array('qrcode', 'any_other_plugin', 'wallabag')
```

### Configuration

Copy `config.php.dist` into `config.php` and setup your instance.

*Wallabag instance URL*
```
$GLOBALS['config']['WALLABAG_URL'] = 'http://v2.wallabag.org' ;
```

*Wallabag version*: either `1` (for 1.x) or `2` (for 2.x)
```
$GLOBALS['config']['WALLABAG_VERSION'] = 2;
```

> Note: these settings can also be set in `data/config.php`.