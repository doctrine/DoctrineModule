# Doctrine Module for Zend Framework 2

Master: [![Build Status](https://secure.travis-ci.org/doctrine/DoctrineModule.png?branch=master)](http://travis-ci.org/doctrine/DoctrineModule)

The Doctrine module intends to integrate Doctrine 2 with Zend Framework 2 quickly and easily.
This module is a provides common Doctrine and ZF integration. To get the most benefit you must use a
provider module such as [DoctrineORMModule](http://www.github.com/doctrine/DoctrineORMModule). The
following features are intended to work out of the box:

  - CLI support for Doctrine 2 ORM and Doctrine MongoDB ODM.
  - Validators for ObjectExists and NoObjectExists
  - Authentication adapter for Zend\Authenticator
  - Support for using existing PDO connections

## Requirements
[Zend Framework 2](http://www.github.com/zendframework/zf2)

## Doctrine CLI
The Doctrine CLI has been pre-configured for you and works as is without any special configuration required for
MongoODM and ORM. It will just use your configuration for those modules.

Access the Doctrine command line through

```sh
./vendor/bin/doctrine-module
```

## Installation

Installation of DoctrineModule uses composer. For composer documentation, please refer to
[getcomposer.org](http://getcomposer.org/).

#### Installation steps

  1. `cd my/project/directory`
  2. create a `composer.json` file with following contents (minimum stability is required since the module still has
     frequent updates):

     ```json
     {
         "minimum-stability": "dev",
         "require": {
             "doctrine/doctrine-module": "dev-master"
         }
     }
     ```
  3. install composer via `curl -s http://getcomposer.org/installer | php` (on windows, download
     http://getcomposer.org/installer and execute it with PHP)
  4. run `php composer.phar install`
  5. open `my/project/directory/configs/application.config.php` and add the following key to your `modules`:

     ```php
     'DoctrineModule',
     ```

#### Installation steps (without composer)

  1. clone this module to `vendor/DoctrineModule`
  2. The module depends on [Doctrine Common 2.3.*](https://github.com/doctrine/common), 
     [Doctrine Data-Fixtures](https://github.com/doctrine/data-fixtures), 
     [Symfony Console >= 2.0.13](https://github.com/symfony/console), 
     [Symfony Yaml >= 2.0.13 ](https://github.com/symfony/yaml),
     [Zend Framework > 2.0.0-beta5 ](https://github.com/zendframework/zf2). You have to download/install those
     packages and have them autoloaded.
  3. open `my/project/directory/configs/application.config.php` and add the following key to your `modules`:

     ```php
     'DoctrineModule',
     ```

## Upgrading
See the [UPGRADE.md](http://www.github.com/doctrine/DoctrineModule/tree/master/docs/UPGRADE.md) file.
