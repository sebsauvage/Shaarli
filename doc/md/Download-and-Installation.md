To install Shaarli, simply place the files in a directory under your webserver's
Document Root (or directly at the document root).

Also, please make sure your server meets the [requirements](Server-requirements)
and is properly [configured](Server-configuration).

Several releases are available:

- by downloading full release archives including all dependencies
- by downloading Github archives
- by cloning the Git repository

---

## Latest release (recommended)
### Download as an archive
Get the latest released version from the [releases](https://github.com/shaarli/Shaarli/releases) page.

**Download our *shaarli-full* archive** to include dependencies.

The current latest released version is `v0.9.1`

Or in command lines:

```bash
$ wget https://github.com/shaarli/Shaarli/releases/download/v0.9.1/shaarli-v0.9.1-full.zip
$ unzip shaarli-v0.9.1-full.zip
$ mv Shaarli /path/to/shaarli/
```

In most cases, download Shaarli from the [releases](https://github.com/shaarli/Shaarli/releases) page. Cloning using `git` or downloading Github branches as zip files requires additional steps (see below).|

### Using git

```
$ mkdir -p /path/to/shaarli && cd /path/to/shaarli/
$ git clone -b v0.9 https://github.com/shaarli/Shaarli.git .
$ composer install --no-dev --prefer-dist
```

## Stable version

The stable version has been experienced by Shaarli users, and will receive security updates.

### Download as an archive

As a .zip archive:

```bash
$ wget https://github.com/shaarli/Shaarli/archive/stable.zip
$ unzip stable.zip
$ mv Shaarli-stable /path/to/shaarli/
```

As a .tar.gz archive :

```bash
$ wget https://github.com/shaarli/Shaarli/archive/stable.tar.gz
$ tar xvf stable.tar.gz
$ mv Shaarli-stable /path/to/shaarli/
```

### Clone with Git 

[Composer](https://getcomposer.org/) is required to build a functional Shaarli installation when pulling from git.

```bash
$ git clone https://github.com/shaarli/Shaarli.git -b stable /path/to/shaarli/
# install/update third-party dependencies
$ cd /path/to/shaarli/
$ composer install --no-dev --prefer-dist
```

## Development version (mainline)

_Use at your own risk!_

To get the latest changes from the `master` branch:

```bash
# clone the repository  
$ git clone https://github.com/shaarli/Shaarli.git -b master /path/to/shaarli/
# install/update third-party dependencies
$ cd /path/to/shaarli
$ composer install --no-dev --prefer-dist
```

## Finish Installation

Once Shaarli is downloaded and files have been placed at the correct location, open it this location your favorite browser.

![install screenshot](http://i.imgur.com/wuMpDSN.png)

Setup your Shaarli installation, and it's ready to use!

## Updating Shaarli

See [Upgrade and Migration](Upgrade-and-migration)
