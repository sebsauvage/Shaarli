#Server requirements
## PHP
### Release information
- [PHP: Supported versions](http://php.net/supported-versions.php)[](.html)
- [PHP: Unsupported versions](http://php.net/eol.php) _(EOL - End Of Life)_[](.html)
- [PHP 5 Changelog](http://php.net/ChangeLog-5.php)[](.html)
- [PHP: Bugs](https://bugs.php.net/)[](.html)

### Supported versions
Version | Status | Shaarli compatibility
:---:|:---:|:---:
7   | RC2 | planned
5.6 | Supported | :white_check_mark:
5.5 | Supported | :white_check_mark:
5.4 | EOL: 2015-09-14 | :white_check_mark:
5.3 | EOL: 2014-08-14 | :white_check_mark:

See also:
- [Travis configuration](https://github.com/shaarli/Shaarli/blob/master/.travis.yml)[](.html)

PHP 7 information:
- Announcements: [Beta1](http://php.net/archive/2015.php#id2015-07-10-4), [RC1](http://php.net/archive/2015.php#id2015-08-21-1), [RC2](http://php.net/archive/2015.php#id2015-09-04-1)[](.html)
- [TODOLIST](https://wiki.php.net/todo/php70)[](.html)
- [Recent bugs](https://bugs.php.net/search.php?limit=30&order_by=id&direction=DESC&cmd=display&status=Open&bug_type=All&phpver=7.0)[](.html)
- [Git repository](http://git.php.net/?p=php-src.git;a=shortlog;h=refs/heads/PHP-7.0.0)[](.html)

### Extensions
Extension | Required? | Usage
---|:---:|---
[`php-mbstring`](http://php.net/manual/en/book.mbstring.php) | CentOS, Fedora, RHEL, Windows | multibyte (Unicode) string support[](.html)
[`php-gd`](http://php.net/manual/en/book.image.php) | - | thumbnail resizing[](.html)
