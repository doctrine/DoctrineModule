# DoctrineModule for Laminas

[![Build Status](https://github.com/doctrine/DoctrineModule/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/doctrine/DoctrineModule/actions/workflows/continuous-integration.yml?query=branch%3A4.4.x)
[![Code Coverage](https://codecov.io/gh/doctrine/DoctrineModule/branch/4.4.x/graphs/badge.svg)](https://codecov.io/gh/doctrine/DoctrineModule/branch/4.4.x)
[![Latest Stable Version](https://poser.pugx.org/doctrine/doctrine-module/v/stable.png)](https://packagist.org/packages/doctrine/doctrine-module)
[![Total Downloads](https://poser.pugx.org/doctrine/doctrine-module/downloads.png)](https://packagist.org/packages/doctrine/doctrine-module)

DoctrineModule provides basic functionality consumed by
[DoctrineORMModule](http://www.github.com/doctrine/DoctrineORMModule)
(if you want to use [Doctrine ORM](https://github.com/doctrine/orm))
and [DoctrineMongoODMModule](https://github.com/doctrine/DoctrineMongoODMModule)
(if you want to use [Doctrine MongoDB ODM](https://github.com/doctrine/mongodb-odm)).

## Installation

Run the following to install this library using [Composer](https://getcomposer.org/):

```bash
composer require doctrine/doctrine-module
```

### Note on PHP 8.0 or later

This module provides an integration with [laminas-cache](https://docs.laminas.dev/laminas-cache/), which currently comes
with some storage adapters which are not compatible with PHP 8.0 or later. To prevent installation of these unused cache
adapters, you will need to add the following to your `composer.json` file:

```json
    "require": {
         "doctrine/doctrine-module": "^4.4.0"
    },
    "replace": {
        "laminas/laminas-cache-storage-adapter-apc": "*",
        "laminas/laminas-cache-storage-adapter-dba": "*",
        "laminas/laminas-cache-storage-adapter-memcache": "*",
        "laminas/laminas-cache-storage-adapter-memcached": "*",
        "laminas/laminas-cache-storage-adapter-mongodb": "*",
        "laminas/laminas-cache-storage-adapter-wincache": "*",
        "laminas/laminas-cache-storage-adapter-xcache": "*",
        "laminas/laminas-cache-storage-adapter-zend-server": "*"
    }
```

Consult the [laminas-cache documentation](https://docs.laminas.dev/laminas-cache/installation/#avoid-unused-cache-adapters-are-being-installed)
for further information on this issue.

## Documentation

Please check the [documentation on the Doctrine website](https://www.doctrine-project.org/projects/doctrine-module.html)
for more detailed information on features provided by this component. The source files for the documentation can be
found in the [docs directory](./docs/en).
