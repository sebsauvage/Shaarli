#Directory structure
Here is the directory structure of Shaarli and the purpose of the different files:

```bash
	index.php        # Main program
	application/     # Shaarli classes
		├── LinkDB.php
		└── Utils.php
	tests/       # Shaarli unitary & functional tests
		├── LinkDBTest.php
		├── utils  # utilities to ease testing
		│   └── ReferenceLinkDB.php
		└── UtilsTest.php
    COPYING          # Shaarli license
    inc/             # static assets and 3rd party libraries
    	├── awesomplete.*          # tags autocompletion library
    	├── blazy.*                # picture wall lazy image loading library
        ├── shaarli.css, reset.css # Shaarli stylesheet.
        ├── qr.*                   # qr code generation library
        └──rain.tpl.class.php      # RainTPL templating library
    tpl/             # RainTPL templates for Shaarli. They are used to build the pages.
    images/          # Images and icons used in Shaarli
    data/            # data storage: bookmark database, configuration, logs, banlist…
        ├── config.php             # Shaarli configuration (login, password, timezone, title…)
        ├── datastore.php          # Your link database (compressed).
        ├── ipban.php              # IP address ban system data
        ├── lastupdatecheck.txt    # Update check timestamp file
        └──log.txt                 # login/IPban log.
    cache/           # thumbnails cache
                     # This directory is automatically created. You can erase it anytime you want.
    tmp/             # Temporary directory for compiled RainTPL templates.
                     # This directory is automatically created. You can erase it anytime you want.
```
