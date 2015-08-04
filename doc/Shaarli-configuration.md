#Shaarli configuration
## Foreword

**Do not edit configuration options in index.php! Your changes would be lost.** 

Once your Shaarli instance is installed, the file `data/config.php` is generated:
* it contains all settings, and can be edited to customize values
* its values override those defined in `index.php`

## File and directory permissions
The server process running Shaarli must have:
- `read` access to the following resources:
    - PHP scripts: `index.php`, `application/*.php`
    - 3rd party PHP and Javascript libraries: `inc/*.php`, `inc\*.js`
    - static assets:
        - CSS stylesheets: `inc\*.css`
        - `images\*`
    - RainTPL templates: `tpl\*.html`
- `read`, `write` and `execution` access to the following directories:
    - `cache` - thumbnail cache
    - `data` - link data store, configuration options
    - `pagecache` - Atom/RSS feed cache
    - `tmp` - RainTPL page cache

On a Linux distribution:
- the web server user will likely be `www` or `http` (for Apache2)
- it will be a member of a group of the same name: `www:www`, `http:http`
- to give it access to Shaarli, either:
    - unzip Shaarli in the default web server location (usually `/var/www/`) and set the web server user as the owner
    - put users in the same group as the web server, and set the appropriate access rights
- if you have a domain / subdomain to serve Shaarli, [configure the server](Server-configuration) accordingly[](.html)

## Example `data/config.php`
```php
<?php 
// User login
$GLOBALS['login'] = '<login>';[](.html)

// User password hash
$GLOBALS['hash'] = '200c452da46c2f889e5e48c49ef044bcacdcb095';[](.html)

// Password salt
$GLOBALS['salt'] = '13b654102321576033d8473b63a275a1bf94c0f0'; [](.html)

// Local timezone
$GLOBALS['timezone'] = 'Africa/Abidjan';[](.html)
date_default_timezone_set('Africa/Abidjan');

// Shaarli title
$GLOBALS['title'] = 'My Little Shaarly';[](.html)

// Link the Shaarli title points to
$GLOBALS['titleLink'] = '?';[](.html)

// HTTP referer redirector
$GLOBALS['redirector'] = '';[](.html)

// Disable session hijacking
$GLOBALS['disablesessionprotection'] = false; [](.html)

// Whether new links are private by default
$GLOBALS['privateLinkByDefault'] = false;[](.html)

// Subdirectory where Shaarli stores its data files.
// You can change it for better security.
$GLOBALS['config'['DATADIR'] = 'data';]('DATADIR']-=-'data';.html)

// File used to store settings
$GLOBALS['config'['CONFIG_FILE'] = 'data/config.php';]('CONFIG_FILE']-=-'data/config.php';.html)

// File containing the link database
$GLOBALS['config'['DATASTORE'] = 'data/datastore.php';]('DATASTORE']-=-'data/datastore.php';.html)

// Number of links displayed per page
$GLOBALS['config'['LINKS_PER_PAGE'] = 20;]('LINKS_PER_PAGE']-=-20;.html)

// File recording failed login attempts and IP bans
$GLOBALS['config'['IPBANS_FILENAME'] = 'data/ipbans.php';]('IPBANS_FILENAME']-=-'data/ipbans.php';.html)

// Failed login attempts before being banned
$GLOBALS['config'['BAN_AFTER'] = 4;]('BAN_AFTER']-=-4;.html)

// Duration of an IP ban, in seconds (30 minutes)
$GLOBALS['config'['BAN_DURATION'] = 1800;]('BAN_DURATION']-=-1800;.html)

// If set to true, everyone will be able to add, edit and remove links,
// as well as change configuration
$GLOBALS['config'['OPEN_SHAARLI'] = false;]('OPEN_SHAARLI']-=-false;.html)

// Do not show link timestamps
$GLOBALS['config'['HIDE_TIMESTAMPS'] = false;]('HIDE_TIMESTAMPS']-=-false;.html)

// Set to false to disable local thumbnail cache, e.g. due to limited disk quotas
$GLOBALS['config'['ENABLE_THUMBNAILS'] = true;]('ENABLE_THUMBNAILS']-=-true;.html)

// Thumbnail cache directory
$GLOBALS['config'['CACHEDIR'] = 'cache';]('CACHEDIR']-=-'cache';.html)

// Enable feed (rss, atom, dailyrss) cache
$GLOBALS['config'['ENABLE_LOCALCACHE'] = true;]('ENABLE_LOCALCACHE']-=-true;.html)

// Feed cache directory
$GLOBALS['config'['PAGECACHE'] = 'pagecache';]('PAGECACHE']-=-'pagecache';.html)

// RainTPL cache directory (keep the trailing slash!)
$GLOBALS['config'['RAINTPL_TMP'] = 'tmp/';]('RAINTPL_TMP']-=-'tmp/';.html)

// RainTPL template directory (keep the trailing slash!)
$GLOBALS['config'['RAINTPL_TPL'] = 'tpl/';]('RAINTPL_TPL']-=-'tpl/';.html)

// Whether Shaarli checks for new releases at https://github.com/shaarli/Shaarli
$GLOBALS['config'['ENABLE_UPDATECHECK'] = true;]('ENABLE_UPDATECHECK']-=-true;.html)

// File to store the latest Shaarli version
$GLOBALS['config'['UPDATECHECK_FILENAME'] = 'data/lastupdatecheck.txt';]('UPDATECHECK_FILENAME']-=-'data/lastupdatecheck.txt';.html)

// Delay between version checks (requires to be logged in) (24 hours)
$GLOBALS['config'['UPDATECHECK_INTERVAL'] = 86400;]('UPDATECHECK_INTERVAL']-=-86400;.html)

// For each link, display a link to an archived version on archive.org
$GLOBALS['config'['ARCHIVE_ORG'] = false;]('ARCHIVE_ORG']-=-false;.html)

// The RSS item links point:
// true   =>  directly to the link
// false  =>  to the entry on Shaarli (permalink)
$GLOBALS['config'['ENABLE_RSS_PERMALINKS'] = true;]('ENABLE_RSS_PERMALINKS']-=-true;.html)

// Hide all links to non-logged users
$GLOBALS['config'['HIDE_PUBLIC_LINKS'] = false;]('HIDE_PUBLIC_LINKS']-=-false;.html)

$GLOBALS['config'['PUBSUBHUB_URL'] = '';]('PUBSUBHUB_URL']-=-'';.html)

// Show an ATOM Feed button next to the Subscribe (RSS) button.
// ATOM feeds are available at the address ?do=atom regardless of this option.
$GLOBALS['config'['SHOW_ATOM'] = false;]('SHOW_ATOM']-=-false;.html)
?>
```
