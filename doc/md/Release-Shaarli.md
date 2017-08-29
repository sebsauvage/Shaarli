See  [Git - Maintaining a project - Tagging your 
releases](http://git-scm.com/book/en/v2/Distributed-Git-Maintaining-a-Project#Tagging-Your-Releases).

## Prerequisites
This guide assumes that you have:

- a GPG key matching your GitHub authentication credentials
    - i.e., the email address identified by the GPG key is the same as the one in your `~/.gitconfig` 
- a GitHub fork of Shaarli
- a local clone of your Shaarli fork, with the following remotes:
    - `origin` pointing to your GitHub fork
    - `upstream` pointing to the main Shaarli repository
- maintainer permissions on the main Shaarli repository, to:
    - push the signed tag
    - create a new release
- [Composer](https://getcomposer.org/) needs to be installed
- The [venv](https://docs.python.org/3/library/venv.html) Python 3 module needs to be installed for HTML documentation generation.

## GitHub release draft and `CHANGELOG.md`
See http://keepachangelog.com/en/0.3.0/ for changelog formatting.

### GitHub release draft
GitHub allows drafting the release note for the upcoming release, from the [Releases](https://github.com/shaarli/Shaarli/releases) page. This way, the release note can be drafted while contributions are merged to `master`.

### `CHANGELOG.md`
This file should contain the same information as the release note draft for the upcoming version.

Update it to:

- add new entries (additions, fixes, etc.)
- mark the current version as released by setting its date and link
- add a new section for the future unreleased version

```bash
$ cd /path/to/shaarli

$ nano CHANGELOG.md

[...]
## vA.B.C - UNRELEASED
TBA

## [vX.Y.Z](https://github.com/shaarli/Shaarli/releases/tag/vX.Y.Z) - YYYY-MM-DD
[...]
```


## Increment the version code, update docs, create and push a signed tag
### Update the list of Git contributors
```bash
$ make authors
$ git commit -s -m "Update AUTHORS"
```

### Create and merge a Pull Request
This one is pretty straightforward ;-)

### Bump Shaarli version to v0.x branch

```bash
$ git checkout master
$ git fetch upstream
$ git pull upstream master

# IF the branch doesn't exists
$ git checkout -b v0.5
# OR if the branch already exists
$ git checkout v0.5
$ git rebase upstream/master

# Bump shaarli version from dev to 0.5.0, **without the `v`**
$ vim shaarli_version.php
$ git add shaarli_version
$ git commit -s -m "Bump Shaarli version to v0.5.0"
$ git push upstream v0.5
```

### Create and push a signed tag
```bash
# update your local copy
$ git checkout v0.5
$ git fetch upstream
$ git pull upstream v0.5

# create a signed tag
$ git tag -s -m "Release v0.5.0" v0.5.0

# push it to "upstream"
$ git push --tags upstream
```

### Verify a signed tag
[`v0.5.0`](https://github.com/shaarli/Shaarli/releases/tag/v0.5.0) is the first GPG-signed tag pushed on the Community Shaarli.

Let's have a look at its signature!

```bash
$ cd /path/to/shaarli
$ git fetch upstream

# get the SHA1 reference of the tag
$ git show-ref tags/v0.5.0
f7762cf803f03f5caf4b8078359a63783d0090c1 refs/tags/v0.5.0

# verify the tag signature information
$ git verify-tag f7762cf803f03f5caf4b8078359a63783d0090c1
gpg: Signature made Thu 30 Jul 2015 11:46:34 CEST using RSA key ID 4100DF6F
gpg: Good signature from "VirtualTam <virtualtam@flibidi.net>" [ultimate]
```

## Publish the GitHub release
### Update release badges
Update `README.md` so version badges display and point to the newly released Shaarli version(s), in the `master` branch.

### Create a GitHub release from a Git tag
From the previously drafted release:

- edit the release notes (if needed)
- specify the appropriate Git tag
- publish the release
- profit!

### Generate and upload all-in-one release archives
Users with a shared hosting may have:

- no SSH access
- no possibility to install PHP packages or server extensions
- no possibility to run scripts

To ease Shaarli installations, it is possible to generate and upload additional release archives,
that will contain Shaarli code plus all required third-party libraries.

**From the `v0.5` branch:**

```bash
$ make release_archive
```

This will create the following archives:

- `shaarli-vX.Y.Z-full.tar`
- `shaarli-vX.Y.Z-full.zip`

The archives need to be manually uploaded on the previously created GitHub release.

### Update `stable` and `latest` branches

```
$ git checkout latest
# latest release
$ git merge v0.5.0
# fix eventual conflicts
$ make test
$ git push upstream latest
$ git checkout stable
# latest previous major
$ git merge v0.4.5 
# fix eventual conflicts
$ make test
$ git push upstream stable
```
