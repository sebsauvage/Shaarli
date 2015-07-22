#Development
## Guidelines
Please have a look at the following pages:
- [Contributing to Shaarli](https://github.com/shaarli/Shaarli/tree/master/CONTRIBUTING.md)[](.html)
- [Static analysis](Static-analysis.html) - patches should try to stick to the [PHP Standard Recommendations](http://www.php-fig.org/psr/) (PSR), especially:
    - [PSR-1](http://www.php-fig.org/psr/psr-1/) - Basic Coding Standard[](.html)
    - [PSR-2](http://www.php-fig.org/psr/psr-2/) - Coding Style Guide[](.html)
- [Unit tests](Unit-tests.html)
- [GnuPG signature](GnuPG-signature.html) for tags/releases

## Continuous integration tools
### Local development
A [`Makefile`](https://github.com/shaarli/Shaarli/blob/master/Makefile) is available to perform project-related operations:[](.html)
- Documentation - generate a local HTML copy of the GitHub wiki
- [Static analysis](Static-analysis.html) - check that the code is compliant to PHP conventions
- [Unit tests](Unit-tests.html) - ensure there are no regressions introduced by new commits

### Automatic builds
[Travis CI](http://docs.travis-ci.com/) is a Continuous Integration build server, that runs a build:[](.html)
- each time a commit is merged to the mainline (`master` branch)
- each time a Pull Request is submitted or updated

A build is composed of several jobs: one for each supported PHP version (see [Server requirements](Server-requirements.html)).

Each build job:
- updates Composer
- installs 3rd-party test dependencies with Composer
- runs [Unit tests](Unit-tests.html)

After all jobs have finished, Travis returns the results to GitHub:
- a status icon represents the result for the `master` branch: [![(https://api.travis-ci.org/shaarli/Shaarli.svg)](https://travis-ci.org/shaarli/Shaarli)]((https://api.travis-ci.org/shaarli/Shaarli.svg)](https://travis-ci.org/shaarli/Shaarli).html)
- Pull Requests are updated with the Travis result
    - Green: all tests have passed
    - Red: some tests failed
    - Orange: tests are pending
