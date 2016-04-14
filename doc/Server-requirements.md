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
7.0 | Supported | :white_check_mark:
5.6 | Supported | :white_check_mark:
5.5 | Supported | :white_check_mark:
5.4 | EOL: 2015-09-14 | :white_check_mark:
5.3 | EOL: 2014-08-14 | :white_check_mark:

See also:
- [Travis configuration](https://github.com/shaarli/Shaarli/blob/master/.travis.yml)[](.html)

### Extensions
Extension | Required? | Usage
---|:---:|---
[`openssl`](http://php.net/manual/en/book.openssl.php) | All | OpenSSL, HTTPS[](.html)
[`php-mbstring`](http://php.net/manual/en/book.mbstring.php) | CentOS, Fedora, RHEL, Windows | multibyte (Unicode) string support[](.html)
[`php-gd`](http://php.net/manual/en/book.image.php) | - | thumbnail resizing[](.html)
[`php-intl`](http://php.net/manual/fr/book.intl.php) | Optional | Tag cloud intelligent sorting (eg. `e->è->f`)[](.html)
