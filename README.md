# DoctrineModule for Laminas

[![Master Branch Build Status](https://secure.travis-ci.org/doctrine/DoctrineModule.png?branch=master)](http://travis-ci.org/doctrine/DoctrineModule) [![Code Coverage](https://codecov.io/gh/doctrine/DoctrineModule/branch/master)](https://codecov.io/gh/doctrine/DoctrineModule/branch/master)

DoctrineModule provides basic functionality consumed by
[DoctrineORMModule](http://www.github.com/doctrine/DoctrineORMModule)
(if you want to use [Doctrine ORM](https://github.com/doctrine/doctrine2))
and [DoctrineMongoODMModule](https://github.com/doctrine/DoctrineMongoODMModule)
(if you want to use [MongoDB ODM](https://github.com/doctrine/mongodb-odm))

## Versions

The most recent versions of this module are on the **4.0.x** branch.
There is a 4.1.x branch and release but this is reserved for future integrations with unreleased Doctrine projects.
If you are including this module in your composer file then use a **4.0.x** release.

## Documentation

Please check the [`docs` dir](https://github.com/doctrine/DoctrineModule/tree/master/docs)
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
