#Server requirements
## PHP
### Release information
- [PHP: Supported versions](http://php.net/supported-versions.php)[](.html)
- [PHP: Unsupported versions](http://php.net/eol.php) _(EOL - End Of Life)_[](.html)
- [PHP 7 Changelog](http://php.net/ChangeLog-7.php)[](.html)
- [PHP 5 Changelog](http://php.net/ChangeLog-5.php)[](.html)
- [PHP: Bugs](https://bugs.php.net/)[](.html)

### Supported versions
Version | Status | Shaarli compatibility
:---:|:---:|:---:
7.1 | Supported (v0.9.x) | :white_check_mark:
7.0 | Supported | :white_check_mark:
5.6 | Supported | :white_check_mark:
5.5 | EOL: 2016-07-10 | :white_check_mark:
5.4 | EOL: 2015-09-14 | :white_check_mark: (up to Shaarli 0.8.x)
5.3 | EOL: 2014-08-14 | :white_check_mark: (up to Shaarli 0.8.x)

See also:
- [Travis configuration](https://github.com/shaarli/Shaarli/blob/master/.travis.yml)[](.html)

### Dependency management
Starting with Shaarli `v0.8.x`, [Composer](https://getcomposer.org/) is used to resolve,[](.html)
download and install third-party PHP dependencies.

Library | Required? | Usage
---|:---:|---
[`shaarli/netscape-bookmark-parser`](https://packagist.org/packages/shaarli/netscape-bookmark-parser) | All | Import bookmarks from Netscape files[](.html)
[`erusev/parsedown`](https://packagist.org/packages/erusev/parsedown) | All | Parse MarkDown syntax for the MarkDown plugin[](.html)
[`slim/slim`](https://packagist.org/packages/slim/slim) | All | Handle routes and middleware for the REST API[](.html)

### Extensions
Extension | Required? | Usage
---|:---:|---
[`openssl`](http://php.net/manual/en/book.openssl.php) | All | OpenSSL, HTTPS[](.html)
[`php-mbstring`](http://php.net/manual/en/book.mbstring.php) | CentOS, Fedora, RHEL, Windows | multibyte (Unicode) string support[](.html)
[`php-gd`](http://php.net/manual/en/book.image.php) | optional | thumbnail resizing[](.html)
[`php-intl`](http://php.net/manual/en/book.intl.php) | optional | localized text sorting (e.g. `e->è->f`)[](.html)
[`php-curl`](http://php.net/manual/en/book.curl.php) | optional | using cURL for fetching webpages and thumbnails in a more robust way[](.html)
