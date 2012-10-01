# DoctrineModule for Zend Framework 2 [![Master Branch Build Status](https://secure.travis-ci.org/doctrine/DoctrineModule.png?branch=master)](http://travis-ci.org/doctrine/DoctrineModule)

DoctrineModule provides basic functionality consumed by 
[DoctrineORMModule](http://www.github.com/doctrine/DoctrineORMModule) 
(if you want to use [Doctrine ORM](https://github.com/doctrine/doctrine2))
and [DoctrineMongoODMModule](https://github.com/doctrine/DoctrineMongoODMModule)
(if you want to use [MongoDB ODM](https://github.com/doctrine/mongodb-odm))

## Documentation

Please check the [`docs` dir](https://github.com/doctrine/DoctrineModule/tree/master/docs)
for more detailed documentation on the features provided by this module.

## Installation

Installation of DoctrineModule uses composer. For composer documentation, please refer to
[getcomposer.org](http://getcomposer.org/).

  1. `cd my/project/directory`
  2. create or modify the `composer.json` file within your ZF2 application file with 
     following contents:

     ```json
     {
         "minimum-stability": "dev",
         "require": {
             "doctrine/doctrine-module": "dev-master"
         }
     }
     ```
  3. install composer via `curl -s https://getcomposer.org/installer | php` (on windows, download
     https://getcomposer.org/installer and execute it with PHP). Then run `php composer.phar install`
  4. open `my/project/directory/configs/application.config.php` and add the following key to your `modules`:

     ```php
     'DoctrineModule',
     ```

##Installation (without composer)

  1. clone this repository to `vendor/DoctrineModule` in your ZF2 application
  2. The module depends on various packages that you have to install and autoload in order to get 
     it to work. Check the `require` section of the
     [`composer.json`](https://github.com/doctrine/DoctrineORMModule/blob/master/composer.json)
     file to see what these requirements are.
  3. open `my/project/directory/configs/application.config.php` and add the following key to your `modules`:

     ```php
     'DoctrineModule',
     ```

