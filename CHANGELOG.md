# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [v0.8.0](https://github.com/shaarli/Shaarli/releases/tag/v0.8.0) - 2016-10-12
Shaarli now uses [Composer](https://getcomposer.org/) to handle its dependencies.
Please use our release archives, or follow the [installation documentation](https://github.com/shaarli/Shaarli/wiki/Download-and-Installation).

### Added
- Composer is required to resolve Shaarli's PHP dependencies
- Shaarli now supports `#hashtags`
- Firefox social share now uses selected text as a description
- Plugin parameters can have a description in each plugin's `.meta` file

### Changed
- Configuration is now stored as a JSON file
- Previous configuration format will be automatically updated (PHP -> JSON)
- Shaarli now defaults to cURL to fetch shaare titles
- URL cleanup: remove `PHPSESSID` parameter
-  `nomarkdown` tag is no longer private, and now affects visitors
- Cleanup template indentation
- Rewrite bookmark import using a generic Netscape parser

### Removed
- Shaarli no longer references Delicious in its description

### Deprecated
- Shaarli configuration is not held as PHP globals anymore

### Fixed
- Ignore case for tags in autocompletion and cloud tag
- Avoid generating empty tags
- Fix a Dockerfile syntax error

### Security
- Fixed a bug preventing to change password
- XSRF token now generated each time a page is rendered

## [v0.7.0](https://github.com/shaarli/Shaarli/releases/tag/v0.7.0) - 2016-05-14
### Added
- Adds an option to encode redirector URL parameter
- Atom/RSS feeds now support Markdown formatting, and plugins in general
- Markdown: use the tag `.nomarkdown` to avoid markdown processing
- Prefill the login field when the authentication has failed
- Show a private links counter

### Changed
- Allow to use the bookmarklet in Firefox reader view (URL clean up)
- Improve tagcloud font size
- Improve title retrieving
- Markdown: inline code background color
- Refactor Netscape bookmark export
- Refactor Atom/RSS feed generation

### Removed
- Remove delicious from Shaarli description

### Fixed
- Fix bad login redirections causing a 404 in a few cases
- Fix tagcloud font-size with French locale
- Don't display empty tags in tag search
- Fix Awesomeplete conflicts with jQuery
- Fix UTC timezone selection
- Fix a bug preventing to import notes in browsers from bookmarks export
- Don't redirect to ?post if ?addlink is reached while logged out

## [v0.6.5](https://github.com/shaarli/Shaarli/releases/tag/v0.6.5) - 2016-03-02
### Fixed
- Fixes a regression generating an unnecessary warning (language in HTTP request).
- Fixes a bug where going through multiple reverse proxy could generate malformed URL.
- Markdown: Fixes a bug where empty description blocks were displayed.

## [v0.6.4](https://github.com/shaarli/Shaarli/releases/tag/v0.6.4) - 2016-02-28
### Added
- Add an updater class to automate user data upgrades
- Plugin admin page: adds a label for checkboxes and improve name display
- Plugin Wallabag: API version can be specified in plugin admin page

### Changed
- Better tag cloud sorting, including special chars (`a > E > é > z`)
- Autolocale now sets all locale categories, not just time
- Use PHP's DateTime object instead of custom functions
- Plugin hooks: process includes before header/footer
- Markdown plugin: better styles for `<code>` and `<pre>` tags
- Improve searching:
    - search terms are now considered separated and won't only return exact results anymore
    - exact search can be done with quotes `"this exact sentence"`
    - search supports excluded terms starting a dash `-exclude`
    - implement crossed search: terms + tags
    - all of them combined across all shaare fields
- New tag behaviour:
    - tags starting with a dash will be renamed without it
    - tags starting with a dot `.` will be hidden unless the user is logged in

### Fixed
- Fix Markdown plugin escape issues (code/quote blocks, etc.)
- Link description aren't trimmed anymore to allow markdown format at the beginning of a shaare
- Fixes plugin admin redirection page on error

### Security
- Fix a bug where non initialized variables were causing a warning
- Fix a bug where saving a link after edit could cause a 404 error

## [v0.6.3](https://github.com/shaarli/Shaarli/releases/tag/v0.6.3) - 2016-01-31
### Added
- Plugins administration page
- Markdown plugin added for shaares description
- Docker: Dockerfile is now in the main git repository and improved
- Add a `.gitattributes` to ease repository management
- Travis: include file permission checks

### Changed
- Auto retrieve of title know works with websites (HTTPS, follow redirections, etc.)
- 404 page is now handled in a template
- Date in log files updated to work with fail2ban
- Wallabag: support of Wallabag v2 and minor fixes
- Link search refactoring
- Logging function refactoring

### Fixed
- Fix a bug where renaming a tag was causing a 404
- Fix a bug allowing to search blank terms
- Fix a bug preventing to remove a tag with special chars when searching 


## [v0.6.2](https://github.com/shaarli/Shaarli/releases/tag/v0.6.2) - 2015-12-23
### Changed
- Plugins: new footer hook
- Plugins: improve QR code
- Cleanup templates

### Fixed
- Plugins: use the actual link URL to generate QR codes
- Templates: missing/erroneous page titles
- Templates: missing variables resulting in PHP errors

### Security
- Fix invalid file permissions (remove executable bit)


## [v0.6.1](https://github.com/shaarli/Shaarli/releases/tag/v0.6.1) - 2015-12-01
### Added
- Add OpenSearch support
- Add a Doxygen makefile target
- Tools: add fine-grained file/directory permission checks (installation)

### Changed
- Tools: check the 'stable' branch for new versions (updates)
- Cleanup: introduce an `ApplicationUtils` class

### Removed
 - Cleanup: remove `json_encode()` function (built-in since PHP 5.2)

### Fixed
 - Auto-complete more than one tag
 - Bookmarklet: support titles containing quotes
 - URL encode links when setting a redirector

## [v0.6.0](https://github.com/shaarli/Shaarli/releases/tag/v0.6.0) - 2015-11-18
### Added
- Introduce a plugin system
- Add a demo_plugin
- Add plugins:
    - addlink_toolbar
    - archiveorg
    - playvideos
    - qrcode
    - readityourself
    - wallabag

### Changed
- Coding style

### Fixed
- Adding a new link now returns the correct anchor in the URL
- Set default file permissions


## [v0.5.4](https://github.com/shaarli/Shaarli/releases/tag/v0.5.4) - 2015-09-15
### Added
- HTTPS: support being served behing an SSL-enabled proxy

### Changed
- HTTP/Server utilities: refactor & add test coverage
- Project & documentation:
    - improve/rewrite `README.md`
    - update contributor list
    - update `index.php` header

### Fixed
- PHP session IDs: handle hash algorithms and bits per char representations


## [v0.5.3](https://github.com/shaarli/Shaarli/releases/tag/v0.5.3) - 2015-09-02
### Fixed
- Fix a bug that could prevent user to login

## [0.5.3](https://github.com/shaarli/Shaarli/releases/tag/0.5.3) - 2015-09-02
This release has been YANKED as it points to a tag that does not follow our naming convention. Please use `v0.5.3` instead.

### Fixed
- Allow uppercase letters in PHP sessionid format

## [v0.5.2](https://github.com/shaarli/Shaarli/releases/tag/v0.5.2) - 2015-08-31
### Added
- Add PHP 7 to Travis platforms

### Changed
- Also extract HTTPS page metadata (title)

### Fixed
- Fix regression preventing to load LinkDB info when adding an existing link

### Security
- Fix Full Path Disclosure upon cookie forgery

## [v0.5.1](https://github.com/shaarli/Shaarli/releases/tag/v0.5.1) - 2015-08-17
### Added
- Add a link to the shaarli/shaarli DockerHub repository

### Changed
- Update local documentation
- Improve timezone detection at installation
- Improve feed cache handling
- Improve URL cleanup for new links

### Fixed
- Fix 404 after editing a link while being logged out

## [v0.5.0](https://github.com/shaarli/Shaarli/releases/tag/v0.5.0) - 2015-07-31
### Added
- Add Firefox Social API
- Start code refactoring:
    - add unit test coverage
    - add Travis integration

### Changed
- Search/Filter by tag fieds can now be accessed quickly with the `Tab` key
- Update documentation
- Remove duplicate tags in links
- Remove annoying URL patterns
- Start code refactoring:
    - move all settings to `data/config.php`
    - refactor Config, LinkDB, TimeZone, Utils

### Fixed
- Fix locale handling
- Fix note URLs
- Fix page redirections
- Fix daily RSS browsing
- Fix title display
- Restore compatibility with PHP 5.3

### Security
- Fix links not being hidden when `HIDE_PUBLIC_LINKS` is set

## [v0.0.45beta](https://github.com/shaarli/Shaarli/releases/tag/v0.0.45beta) - 2015-03-16
### Fixed
- Fix improperly displayed Unicode character
- Fix incorrect font size for "Add link" input field

## [v0.0.44beta](https://github.com/shaarli/Shaarli/releases/tag/v0.0.44beta) - 2015-03-15
### Added
- Add a Makefile to run static code checkers
- Add local documentation (help link in page footer)
- Use awesomplete library for autocompletion
- Use bLazy.js library for images lazy loading
- New 'Add Note' bookmarklet to immediatly open a note (text post) compose window

### Changed
- Theme improvements and cleanup (menu, search fields, icons, linklist...)
- Allow 'javascript:' links sharing (bookmarklets)
- Make update check optional
- Redirect to homepage after adding a link via "Add Link" dialog
- Remove more annoying URL parameters for shared links
- Code cleanup

### Removed
- Remove jQuery

### Security
- Don't disclose version to visitors (shaarli-version.txt)

## [v0.0.43beta](https://github.com/shaarli/Shaarli/releases/tag/v0.0.43beta) - 2015-02-20
### Added
- Title button link URL is now configurable
- RainTPL's TMP and TPL directories path are now configurable
- Displayed URLs for each link are now clickable links
- Show links timestamps in Daily view

### Changed
- Automatically prepend "Note:" to title of self-posts (posts not pointing to an URL)
- Make ATOM toolbar button optional (`SHOW_ATOM` configuration variable)
- Optional archive.org links for each Shaarli link (`ARCHIVE_ORG` option)
- Thumbnails: force HTTPS when possible
- Improve tag cloud font scaling
- Allow pointing RSS items to the permalink instead of the direct URL (`ENABLE_RSS_PERMALINKS` option)
- Update JS libraries and add version numbers in filenames
- Updates to README and footer

### Fixed
- Fix problems when running Shaarli behind a reverse proxy (invalid RSS feed URL)
- Update check now checks against the community fork version
- Include cache/ data/ pagecache/ and tmp/ directories in the repository
- Fix duplicate tag search returning no results
- Fix unnecessary 404 error on "Add link" when the user is logged out
- Fixes to copyright/licensing information and unlicensed media
- Fixes for tag cloud invalid links
- Coding style fixes/cleanup
- Fix redirection after deleteing a link leading to a 404 error
- Shaarli's HTML is now W3C-compliant
- Search now works with Unicode characters

### Security
- Do not leak server's PHP version and Shaarli's full path on errors
- Prevent Shaarli from sending a lot of duplicate cookies
