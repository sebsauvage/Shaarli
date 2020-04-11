The testing framework used is [PHPUnit](https://phpunit.de/); it can be installed with [Composer](https://getcomposer.org/), which is a dependency management tool.

## Setup a testing environment

### Install composer

You can either use:

- a system-wide version, e.g. installed through your distro's package manager (eg. `sudo apt install composer`)
- a local version, downloadable [here](https://getcomposer.org/download/). To update a local composer installation, run `php composer.phar self-update`


### Install Shaarli development dependencies

```bash
$ cd /path/to/shaarli
$ composer install
```

### Install Xdebug

Xdebug must be installed and enable for PHPUnit to generate coverage reports. See http://xdebug.org/docs/install.

```bash
# for Debian-based distributions
$ aptitude install php-xdebug

# for ArchLinux:
$ pacman -S xdebug
```

Then add the following line to `/etc/php/<PHP_VERSION>/cli/php.ini`:

```ini
zend_extension=xdebug.so
```

## Run unit tests

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

### Running tests inside Docker containers

Test Dockerfiles are located under `tests/docker/<distribution>/Dockerfile`,
and can be used to build Docker images to run Shaarli test suites under common
Linux environments.

Dockerfiles are provided for the following environments:

- `alpine36` - [Alpine 3.6](https://www.alpinelinux.org/downloads/)
- `debian8` - [Debian 8 Jessie](https://www.debian.org/DebianJessie) (oldstable)
- `debian9` - [Debian 9 Stretch](https://wiki.debian.org/DebianStretch) (stable)
- `ubuntu16` - [Ubuntu 16.04 Xenial Xerus](http://releases.ubuntu.com/16.04/) (LTS)

What's behind the curtains:

- each image provides:
    - a base Linux OS
    - Shaarli PHP dependencies (OS packages)
    - test PHP dependencies (OS packages)
    - Composer
- the local workspace is mapped to the container's `/shaarli/` directory,
- the files are rsync'd so tests are run using a standard Linux user account
  (running tests as `root` would bypass permission checks and may hide issues)
- the tests are run inside the container.

To run tests inside a Docker container:

```bash
# build the Debian 9 Docker image for unit tests
$ cd /path/to/shaarli
$ cd tests/docker/debian9
$ docker build -t shaarli-test:debian9 .

# install/update 3rd-party test dependencies
$ composer install --prefer-dist

# run tests using the freshly built image
$ docker run -v $PWD:/shaarli shaarli-test:debian9 docker_test

# run the full test campaign
$ docker run -v $PWD:/shaarli shaarli-test:debian9 docker_all_tests
```
