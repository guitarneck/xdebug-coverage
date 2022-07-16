[//]: # (xdebug-coverage v1.0.0)
[![Version 1.0.0][version-badge]][changelog] ![GitHub release (latest by date)][github-release-url] ![GitHub last commit][github-last-commit]
![Packagist Version][packagist-version-url] ![Packagist PHP Version Support][packagist-version-support-url] ![Packagist Downloads][packagist-downloads-url]
![GitHub license][github-license-url] [![Keep a Changelog v1.1.0][changelog-badge]][changelog]


[//]: # (TODO: [![Build Status][travis-image]][travis-url] [![Coverage Status][coveralls-image]][coveralls-url])

---

# XDebugCoverage

A [PHP][php-url] framework to run [XDebug][xdebug-url] Code Coverage and get reports of the result.

---

# Table of Contents

* [Install](#install)
* [Command](#command)
    * [Options](#options)
    * [Configuration](#configuration)
* [Using .dot](#using-dot)
* [License](#license)

---

## Install

```shell
composer require guitarneck/xdebug-coverage --dev
```

## Command

```shell
$ coverage/coverage tests/Something.test.php [options]
```

### Options

```text
   --debug

   --excludes=,--excludes,-x              The paths to exclude. Separated by ','.
                                          Ex: vendor/,tests/,inc/lib/

   --format=,--format,-F                  The file format to be generated.

   --help,-h                              This help page

   --includes=,--includes,-i              The paths to include. Separated by ','.
                                          Ex: src/,inc/

   --no-extra-filter

   --output-path=,--output-path,-p        The paths to output. Separated by ','.
                                          Ex: {DIR},..,reports
                                          - {DIR}: __DIR__ ('coverage/sources')
                                          - ..   : parent path
```

### configuration

Default configuration can be sets in _coverage/sources/XDebugCoverage.json_

## Using .dot

[Grahpviz](https://graphviz.org/) is open source graph visualization software.

```shell
$ dot -Tsvg coverage\\reports\\Hello.dot > Hello.svg
```
---

# License

[MIT © guitarneck][license]

[github-license-url]: https://img.shields.io/github/license/guitarneck/xdebug-coverage
[github-release-url]: https://img.shields.io/github/v/release/guitarneck/xdebug-coverage
[github-last-commit]: https://img.shields.io/github/last-commit/guitarneck/xdebug-coverage

[license]: ./LICENSE
[license-badge]: https://img.shields.io/badge/license-MIT-blue.svg

[version-badge]: https://img.shields.io/badge/version-1.0.0-blue.svg

[changelog]: ./CHANGELOG.md
[changelog-badge]: https://img.shields.io/badge/changelog-Keep%20a%20Changelog%20v1.1.0-%23E05735

[packagist-version-url]: https://img.shields.io/packagist/v/guitarneck/xdebug-coverage
[packagist-downloads-url]: https://img.shields.io/packagist/dt/guitarneck/xdebug-coverage

[php-url]: https://www.php.net/
[xdebug-url]: https://xdebug.org/

[packagist-url]: https://packagist.org/packages/guitarneck/xdebug-coverage
[packagist-version-support-url]: https://img.shields.io/packagist/php-v/guitarneck/xdebug-coverage/1.0.0

[travis-image]: https://img.shields.io/travis/guitarneck/xdebug-coverage.svg?label=travis-ci
[travis-url]: https://travis-ci.org/guitarneck/xdebug-coverage

[coveralls-image]: https://coveralls.io/repos/github/guitarneck/xdebug-coverage/badge.svg?branch=master
[coveralls-url]: https://coveralls.io/github/guitarneck/xdebug-coverage?branch=master

[pull-request]: https://help.github.com/articles/creating-a-pull-request/
[fork]: https://help.github.com/articles/fork-a-repo/