## Foreword

**Do not edit configuration options in index.php! Your changes would be lost.** 

Once your Shaarli instance is installed, the file `data/config.json.php` is generated:
* it contains all settings in JSON format, and can be edited to customize values
* it defines which [plugins](Plugin-System) are enabled
* its values override those defined in `index.php`
* it is wrap in a PHP comment to prevent anyone accessing it, regardless of server configuration

## File and directory permissions

The server process running Shaarli must have:

- `read` access to the following resources:
    - PHP scripts: `index.php`, `application/*.php`, `plugins/*.php`
    - 3rd party PHP and Javascript libraries: `inc/*.php`, `inc/*.js`
    - static assets:
        - CSS stylesheets: `inc/*.css`
        - `images/*`
    - RainTPL templates: `tpl/*.html`
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
- if you have a domain / subdomain to serve Shaarli, [configure the server](Server-configuration) accordingly

## Configuration

In `data/config.json.php`.

See also [Plugin System](Plugin-System).

### Credentials
 
_These settings should not be edited_

- **login**: Login username.  
- **hash**: Generated password hash.  
- **salt**: Password salt.

### General

- **title**: Shaarli's instance title.  
- **header_link**: Link to the homepage.  
- **links_per_page**: Number of shaares displayed per page.  
- **timezone**: See [the list of supported timezones](http://php.net/manual/en/timezones.php).  
- **enabled_plugins**: List of enabled plugins.
- **default_note_title**: Default title of a new note.
- **retrieve_description** (boolean): If set to true, for every new links Shaarli will try
to retrieve the description and keywords from the HTML meta tags.

### Security

- **session_protection_disabled**: Disable session cookie hijacking protection (not recommended). 
  It might be useful if your IP adress often changes.  
- **ban_after**: Failed login attempts before being IP banned.  
- **ban_duration**: IP ban duration in seconds.  
- **open_shaarli**: Anyone can add a new link while logged out if enabled.  
- **trusted_proxies**: List of trusted IP which won't be banned after failed login attemps. Useful if Shaarli is behind a reverse proxy.  
- **allowed_protocols**: List of allowed protocols in shaare URLs or markdown-rendered descriptions. Useful if you want to store `javascript:` links (bookmarklets) in Shaarli (default: `["ftp", "ftps", "magnet"]`).

### Resources

- **data_dir**: Data directory.  
- **datastore**: Shaarli's links database file path.  
- **history**: Shaarli's operation history file path.
- **updates**: File path for the ran updates file.  
- **log**: Log file path.  
- **update_check**: Last update check file path.  
- **raintpl_tpl**: Templates directory.  
- **raintpl_tmp**: Template engine cache directory.  
- **thumbnails_cache**: Thumbnails cache directory.  
- **page_cache**: Shaarli's internal cache directory.  
- **ban_file**: Banned IP file path.

### Translation

- **language**: translation language (also see [Translations](Translations))
    - **auto** (default): The translation language is chosen from the browser locale. 
    It means that the language can be different for 2 different visitors depending on their locale.  
    - **en**: Use the English translation.
    - **fr**: Use the French translation.
- **mode**: 
    - **auto** or **php** (default): Use the PHP implementation of gettext (slower)
    - **gettext**: Use PHP builtin gettext extension 
    (faster, but requires `php-gettext` to be installed and to reload the web server on update)
- **extension**: Translation extensions for custom themes or plugins. 
Must be an associative array: `translation domain => translation path`.

### Updates

- **check_updates**: Enable or disable update check to the git repository.  
- **check_updates_branch**: Git branch used to check updates (e.g. `stable` or `master`).  
- **check_updates_interval**: Look for new version every N seconds (default: every day).

### Privacy

- **default_private_links**: Check the private checkbox by default for every new link.  
- **hide_public_links**: All links are hidden while logged out.  
- **force_login**: if **hide_public_links** and this are set to `true`, all anonymous users are redirected to the login page.
- **hide_timestamps**: Timestamps are hidden.
- **remember_user_default**: Default state of the login page's *remember me* checkbox
    - `true`: checked by default, `false`: unchecked by default

### Feed

- **rss_permalinks**: Enable this to redirect RSS links to Shaarli's permalinks instead of shaared URL.  
- **show_atom**: Display ATOM feed button.

### Thumbnail

- **enable_thumbnails**: Enable or disable thumbnail display.  
- **enable_localcache**: Enable or disable local cache.

## Configuration file example

```json
<?php /*
{
    "credentials": {
        "login": "<login>",
        "hash": "<password hash>",
        "salt": "<password salt>"
    },
    "security": {
        "ban_after": 4,
        "session_protection_disabled": false,
        "ban_duration": 1800,
        "trusted_proxies": [
            "1.2.3.4",
            "5.6.7.8"
        ],
        "allowed_protocols": [
            "ftp",
            "ftps",
            "magnet"
        ]
    },
    "resources": {
        "data_dir": "data",
        "config": "data\/config.php",
        "datastore": "data\/datastore.php",
        "ban_file": "data\/ipbans.php",
        "updates": "data\/updates.txt",
        "log": "data\/log.txt",
        "update_check": "data\/lastupdatecheck.txt",
        "raintpl_tmp": "tmp\/",
        "raintpl_tpl": "tpl\/",
        "thumbnails_cache": "cache",
        "page_cache": "pagecache"
    },
    "general": {
        "check_updates": true,
        "rss_permalinks": true,
        "links_per_page": 20,
        "default_private_links": true,
        "enable_thumbnails": true,
        "enable_localcache": true,
        "check_updates_branch": "stable",
        "check_updates_interval": 86400,
        "enabled_plugins": [
            "markdown",
            "wallabag",
            "archiveorg"
        ],
        "timezone": "Europe\/Paris",
        "title": "My Shaarli",
        "header_link": "?"
    },
    "extras": {
        "show_atom": false,
        "hide_public_links": false,
        "hide_timestamps": false,
        "open_shaarli": false,
    },
    "general": {
        "header_link": "?",
        "links_per_page": 20,
        "enabled_plugins": [
            "markdown",
            "wallabag"
        ],
        "timezone": "Europe\/Paris",
        "title": "My Shaarli"
    },
    "updates": {
        "check_updates": true,
        "check_updates_branch": "stable",
        "check_updates_interval": 86400
    },
    "feed": {
        "rss_permalinks": true,
        "show_atom": false
    },
    "privacy": {
        "default_private_links": true,
        "hide_public_links": false,
        "force_login": false,
        "hide_timestamps": false,
        "remember_user_default": true
    },
    "thumbnail": {
        "enable_thumbnails": true,
        "enable_localcache": true
    },
    "plugins": {
        "WALLABAG_URL": "http://demo.wallabag.org",
        "WALLABAG_VERSION": "1"
    },
    "translation": {
        "language": "fr",
        "mode": "php",
        "extensions": {
            "demo": "plugins/demo_plugin/languages/"
        }
    }
} ?>
```

## Additional configuration

The `playvideos` plugin may require that you adapt your server's 
[Content Security Policy](https://github.com/shaarli/Shaarli/blob/master/plugins/playvideos/README.md#troubleshooting) 
configuration to work properly.

