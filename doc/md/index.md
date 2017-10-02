# [Shaarli](https://github.com/shaarli/Shaarli/) documentation

Here you can find some info on how to use, configure, tweak and solve problems with your Shaarli.

For general info, read the [README](https://github.com/shaarli/Shaarli/blob/master/README.md).

If you have any questions or ideas, please join the [chat](https://gitter.im/shaarli/Shaarli) (also reachable via [IRC](https://irc.gitter.im/)), post them in our [general discussion](https://github.com/shaarli/Shaarli/issues/308) or read the current [issues](https://github.com/shaarli/Shaarli/issues).
If you've found a bug, please create a [new issue](https://github.com/shaarli/Shaarli/issues/new).

If you would like a feature added to Shaarli, check the issues labeled [`feature`](https://github.com/shaarli/Shaarli/labels/feature), [`enhancement`](https://github.com/shaarli/Shaarli/labels/enhancement), and [`plugin`](https://github.com/shaarli/Shaarli/labels/plugin).

_Note: This documentation is available online at https://shaarli.readthedocs.io/, and locally in the `doc/html/` directory of your Shaarli installation._

[![Join the chat at https://gitter.im/shaarli/Shaarli](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/shaarli/Shaarli)
[![Bountysource](https://www.bountysource.com/badge/team?team_id=19583&style=bounties_received)](https://www.bountysource.com/teams/shaarli/issues)
[![Docker repository](https://img.shields.io/docker/pulls/shaarli/shaarli.svg)](https://hub.docker.com/r/shaarli/shaarli/)

### Demo

You can use this [public demo instance of Shaarli](https://demo.shaarli.org).
It runs the latest development version of Shaarli and is updated/reset daily.

Login: `demo`; Password: `demo`

Docker users can start a personal instance from an [autobuild image](https://hub.docker.com/r/shaarli/shaarli/). For example to start a temporary Shaarli at ``localhost:8000``, and keep session data (config, storage):
```
MY_SHAARLI_VOLUME=$(cd /path/to/shaarli/data/ && pwd -P)
docker run -ti --rm \
         -p 8000:80 \
         -v $MY_SHAARLI_VOLUME:/var/www/shaarli/data \
         shaarli/shaarli
```

A brief guide on getting starting using docker is given in [Docker 101](docker/docker-101).
To learn more about user data and how to keep it across versions, please see [Upgrade and Migration](Upgrade-and-migration) documentation.

## Features

### Interface
- minimalist design (simple is beautiful)
- FAST
- ATOM and RSS feeds
- views:
    - paginated link list
    - tag cloud
    - picture wall: image and video thumbnails
    - daily: newspaper-like daily digest
    - daily RSS feed
- permalinks for easy reference
- links can be public or private
- extensible through [plugins](https://shaarli.readthedocs.io/en/master/Plugins/#plugin-usage)

### Tag, view and search your links!
- add a custom title and description to archived links
- add tags to classify and search links
    - features tag autocompletion, renaming, merging and deletion
- full-text and tag search

### Easy setup
- dead-simple installation: drop the files, open the page
- links are stored in a file
    - compact storage
    - no database required
    - easy backup: simply copy the datastore file
- import and export links as Netscape bookmarks

### Accessibility
- Firefox bookmarlet to share links in one click
- support for mobile browsers
- works with Javascript disabled
- easy page customization through HTML/CSS/RainTPL

### Security
- bruteforce-proof login form
- protected against [XSRF](http://en.wikipedia.org/wiki/Cross-site_request_forgery)
and session cookie hijacking

### Goodies
- thumbnail generation for images and video services:
dailymotion, flickr, imageshack, imgur, vimeo, xkcd, youtube...
    - lazy-loading with [bLazy](http://dinbror.dk/blazy/)
- [PubSubHubbub](https://code.google.com/p/pubsubhubbub/) protocol support
- URL cleanup: automatic removal of `?utm_source=...`, `fb=...`
- discreet pop-up notification when a new release is available

### REST API

Easily extensible by any client using the REST API exposed by Shaarli.

See the [API documentation](http://shaarli.github.io/api-documentation/).

### Other usages
Though Shaarli is primarily a bookmarking application, it can serve other purposes
(see [Features](Features)):

- micro-blogging
- pastebin
- online notepad
- snippet archive

## About
### Shaarli community fork
This friendly fork is maintained by the Shaarli community at https://github.com/shaarli/Shaarli

This is a community fork of the original [Shaarli](https://github.com/sebsauvage/Shaarli/) project by [Sébastien Sauvage](http://sebsauvage.net/).

The original project is currently unmaintained, and the developer [has informed us](https://github.com/sebsauvage/Shaarli/issues/191)
that he would have no time to work on Shaarli in the near future.
The Shaarli community has carried on the work to provide
[many patches](https://github.com/shaarli/Shaarli/compare/sebsauvage:master...master)
for [bug fixes and enhancements](https://github.com/shaarli/Shaarli/issues?q=is%3Aclosed+)
in this repository, and will keep maintaining the project for the foreseeable future, while keeping Shaarli simple and efficient.

### Contributing
If you'd like to help, please:
- have a look at the open [issues](https://github.com/shaarli/Shaarli/issues)
and [pull requests](https://github.com/shaarli/Shaarli/pulls)
- feel free to report bugs (feedback is much appreciated)
- suggest new features and improvements to both code and [documentation](https://github.com/shaarli/Shaarli/wiki)
- propose solutions to existing problems
- submit pull requests :-)


### License
Shaarli is [Free Software](http://en.wikipedia.org/wiki/Free_software). See [COPYING](COPYING) for a detail of the contributors and licenses for each individual component.

