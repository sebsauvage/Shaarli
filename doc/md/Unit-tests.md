### Setup your environment for tests

The framework used is [PHPUnit](https://phpunit.de/); it can be installed with [Composer](https://getcomposer.org/), which is a dependency management tool.

### Install composer

You can either use:

- a system-wide version, e.g. installed through your distro's package manager
- a local version, downloadable [here](https://getcomposer.org/download/).

```bash
# system-wide version
$ composer install
$ composer update

# local version
$ php composer.phar self-update
$ php composer.phar install
$ php composer.phar update
```

#### Install Shaarli dev dependencies

```bash
$ cd /path/to/shaarli
$ composer update
```

#### Install and enable Xdebug to generate PHPUnit coverage reports

See http://xdebug.org/docs/install

For Debian-based distros:
```bash
$ aptitude install php5-xdebug
```
For ArchLinux:
```bash
$ pacman -S xdebug
```

Then add the following line to `/etc/php/php.ini`:
```ini
zend_extension=xdebug.so
```

#### Run unit tests

Successful test suite:
```bash
$ make test

-------
PHPUNIT
-------
PHPUnit 4.6.9 by Sebastian Bergmann and contributors.

Configuration read from /home/virtualtam/public_html/shaarli/phpunit.xml

....................................

Time: 759 ms, Memory: 8.25Mb

OK (36 tests, 65 assertions)
```

Test suite with failures and errors:
```bash
$ make test
-------
PHPUNIT
-------
PHPUnit 4.6.9 by Sebastian Bergmann and contributors.

Configuration read from /home/virtualtam/public_html/shaarli/phpunit.xml

E..FF...............................

Time: 802 ms, Memory: 8.25Mb

There was 1 error:

1) LinkDBTest::testConstructLoggedIn
Missing argument 2 for LinkDB::__construct(), called in /home/virtualtam/public_html/shaarli/tests/Link\
DBTest.php on line 79 and defined

/home/virtualtam/public_html/shaarli/application/LinkDB.php:58
/home/virtualtam/public_html/shaarli/tests/LinkDBTest.php:79

--

There were 2 failures:

1) LinkDBTest::testCheckDBNew
Failed asserting that two strings are equal.
--- Expected
+++ Actual
@@ @@
-'e3edea8ea7bb50be4bcb404df53fbb4546a7156e'
+'85eab0c610d4f68025f6ed6e6b6b5fabd4b55834'

/home/virtualtam/public_html/shaarli/tests/LinkDBTest.php:121

2) LinkDBTest::testCheckDBLoad
Failed asserting that two strings are equal.
--- Expected
+++ Actual
@@ @@
-'e3edea8ea7bb50be4bcb404df53fbb4546a7156e'
+'85eab0c610d4f68025f6ed6e6b6b5fabd4b55834'

/home/virtualtam/public_html/shaarli/tests/LinkDBTest.php:133

FAILURES!
Tests: 36, Assertions: 63, Errors: 1, Failures: 2.
```

#### Test results and coverage

By default, PHPUnit will run all suitable tests found under the `tests` directory.

Each test has 3 possible outcomes:

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
