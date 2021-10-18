# DoctrineModule for Laminas

[![Build Status](https://github.com/doctrine/DoctrineModule/workflows/Continuous%20Integration/badge.svg)](https://github.com/doctrine/DoctrineModule/actions/workflows/continuous-integration.yml?query=branch%3A4.1.x+)
[![Code Coverage](https://codecov.io/github/doctrine/DoctrineModule/coverage.svg?branch=4.1.x)](https://codecov.io/gh/doctrine/DoctrineModule/branch/4.1.x)

DoctrineModule provides basic functionality consumed by
[DoctrineORMModule](http://www.github.com/doctrine/DoctrineORMModule)
(if you want to use [Doctrine ORM](https://github.com/doctrine/orm))
and [DoctrineMongoODMModule](https://github.com/doctrine/DoctrineMongoODMModule)
(if you want to use [MongoDB ODM](https://github.com/doctrine/mongodb-odm)).

## Documentation

Please check the [`docs` dir](https://github.com/doctrine/DoctrineModule/tree/master/docs/en)
for more detailed documentation on the features provided by this module.

## Installation

Installation of DoctrineModule uses composer. For composer documentation, please refer to
[getcomposer.org](http://getcomposer.org/).

```sh
composer require doctrine/doctrine-module
```

Then add `DoctrineModule` to your `config/application.config.php`

Installation without composer is not officially supported, and requires you to install and autoload
the dependencies specified in the `composer.json`.
