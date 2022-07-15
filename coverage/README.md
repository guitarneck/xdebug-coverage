# XDebugCoverage

A [PHP](https://www.php.net/) framework to run [XDebug](https://xdebug.org/) Code Coverage and get reports of the result.

## Install

```shell
composer require guitarneck/xdebug-coverage --dev
```

## Command

```shell
$ coverage/coverage tests/Something.test.php [options]
```

### _Options_

```
   --debug

    --excludes=,--excludes,-x        The paths to exclude. Separated by ','.
                                     Ex: vendor/,tests/,inc/lib/

    --format=,--format,-F            The file format to be generated.

    --help,-h                        This help page

    --includes=,--includes,-i        The paths to include. Separated by ','.
                                     Ex: src/,inc/

    --no-extra-filter
```

### _configuration_

Default configuration cans be sets in _coverage/sources/XDebugCoverage.json_

## Using .dot

[Grahpviz](https://graphviz.org/) is open source graph visualization software.

```shell
$ dot -Tsvg coverage\\reports\\Hello.dot > Hello.svg
```

# License

[MIT © guitarneck](./LICENSE)