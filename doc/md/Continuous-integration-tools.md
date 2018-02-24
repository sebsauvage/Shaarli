## Local development
A [`Makefile`](https://github.com/shaarli/Shaarli/blob/master/Makefile) is available to perform project-related operations:

- Documentation - generate a local HTML copy of the GitHub wiki
- [Static analysis](Static-analysis) - check that the code is compliant to PHP conventions
- [Unit tests](Unit-tests) - ensure there are no regressions introduced by new commits

## Automatic builds
[Travis CI](http://docs.travis-ci.com/) is a Continuous Integration build server, that runs a build:

- each time a commit is merged to the mainline (`master` branch)
- each time a Pull Request is submitted or updated

A build is composed of several jobs: one for each supported PHP version (see [Server requirements](Server requirements)).

Each build job:

- updates Composer
- installs 3rd-party test dependencies with Composer
- runs [Unit tests](Unit-tests)
- runs ESLint check

After all jobs have finished, Travis returns the results to GitHub:

- a status icon represents the result for the `master` branch: [![](https://api.travis-ci.org/shaarli/Shaarli.svg)](https://travis-ci.org/shaarli/Shaarli)
- Pull Requests are updated with the Travis result
    - Green: all tests have passed
    - Red: some tests failed
    - Orange: tests are pending
