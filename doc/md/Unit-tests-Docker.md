## Running tests inside Docker containers

Read first:

- [Docker 101](docker/docker-101.md)
- [Docker resources](docker/resources.md)
- [Unit tests](Unit-tests.md)

### Docker test images

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

### Building test images

```bash
# build the Debian 9 Docker image
$ cd /path/to/shaarli
$ cd tests/docker/debian9
$ docker build -t shaarli-test:debian9 .
```

### Running tests

```bash
$ cd /path/to/shaarli

# install/update 3rd-party test dependencies
$ composer install --prefer-dist

# run tests using the freshly built image
$ docker run -v $PWD:/shaarli shaarli-test:debian9 docker_test

# run the full test campaign
$ docker run -v $PWD:/shaarli shaarli-test:debian9 docker_all_tests
```
