## Directory structure

Here is the directory structure of Shaarli and the purpose of the different files:

```bash
	index.php        # Main program
	application/     # Shaarli classes
		├── LinkDB.php

        ...

		└── Utils.php
	tests/           # Shaarli unitary & functional tests
		├── LinkDBTest.php

        ...

		├── utils    # utilities to ease testing
		│   └── ReferenceLinkDB.php
		└── UtilsTest.php
	assets/
	    ├── common/                # Assets shared by multiple themes
	        ├── ...
        ├── default/               # Assets for the default template, before compilation
            ├── fonts/                  # Font files
            ├── img/                    # Images used by the default theme
            ├── js/                     # JavaScript files in ES6 syntax
            ├── scss/                   # SASS files
        └── vintage/               # Assets for the vintage template, before compilation
            └── ...
    COPYING          # Shaarli license
    inc/             # static assets and 3rd party libraries
        └── rain.tpl.class.php     # RainTPL templating library
    images/          # Images and icons used in Shaarli
    data/            # data storage: bookmark database, configuration, logs, banlist...
        ├── config.json.php        # Shaarli configuration (login, password, timezone, title...)
        ├── datastore.php          # Your link database (compressed).
        ├── ipban.php              # IP address ban system data
        ├── lastupdatecheck.txt    # Update check timestamp file
        └── log.txt                # login/IPban log.
    tpl/             # RainTPL templates for Shaarli. They are used to build the pages.
        ├── default/               # Default Shaarli theme
            ├── fonts/                  # Font files
            ├── img/                    # Images
            ├── js/                     # JavaScript files compiled by Babel and compatible with all browsers
            ├── css/                    # CSS files compiled with SASS
        └── vintage/               # Legacy Shaarli theme
            └── ...
    cache/           # thumbnails cache
                     # This directory is automatically created. You can erase it anytime you want.
    tmp/             # Temporary directory for compiled RainTPL templates.
                     # This directory is automatically created. You can erase it anytime you want.
    vendor/          # Third-party dependencies. This directory is created by Composer
```
