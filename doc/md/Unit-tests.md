### Setup your environment for tests

The framework used is [PHPUnit](https://phpunit.de/); it can be installed with [Composer](https://getcomposer.org/), which is a dependency management tool.

### Install composer

You can either use:

- a system-wide version, e.g. installed through your distro's package manager (eg. `sudo apt install composer`)
- a local version, downloadable [here](https://getcomposer.org/download/). To update a local composer installation, run `php composer.phar self-update`


#### Install Shaarli dev dependencies

```bash
$ cd /path/to/shaarli
$ composer install
$ composer update
```

#### Install Xdebug

Xdebug must be installed and enable for PHPUnit to generate coverage reports. See http://xdebug.org/docs/install.

```bash
# for Debian-based distributions
$ aptitude install php5-xdebug

# for ArchLinux:
$ pacman -S xdebug
```

Then add the following line to `/etc/php/php.ini`:

```ini
zend_extension=xdebug.so
```

#### Run unit tests

Run `make test` and ensure tests return `OK`. If tests return failures, refer to PHPUnit messages and fix your code/tests accordingly.

By default, PHPUnit will run all suitable tests found under the `tests` directory. Each test has 3 possible outcomes:

- `.` - success
- `F` - failure: the test was run but its results are invalid
    - the code does not behave as expected
    - dependencies to external elements: globals, session, cache...
- `E` - error: something went wrong and the tested code has crashed
    - typos in the code, or in the test code
    - dependencies to missing external elements

If Xdebug has been installed and activated, two coverage reports will be generated:

- a summary in the console
- a detailed HTML report with metrics for tested code
    - to open it in a web browser: `firefox coverage/index.html &`

### Executing specific tests

Add a [`@group`](https://phpunit.de/manual/current/en/appendixes.annotations.html#appendixes.annotations.group) annotation in a test class or method comment:

```php
/**
 * Netscape bookmark import
 * @group WIP
 */
class BookmarkImportTest extends PHPUnit_Framework_TestCase
{
   [...]
}
```

To run all tests annotated with `@group WIP`:
```bash
$ vendor/bin/phpunit --group WIP tests/
```
