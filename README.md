# Doctrine Module for Zend Framework 2
The Doctrine module intends to integrate Doctrine 2 with Zend Framework 2 quickly and easily.
This module is a provides common Doctrine and ZF integration. To get the most benefit you must use a
provider module such as [DoctrineORMModule](http://www.github.com/doctrine/DoctrineORMModule). The 
following features are intended to work out of the box: 
  
  - CLI support for ORM and MongoDB-ODM.
  - Validators for EntityExists and NoEntityExists
  - Authentication adapter for Zend\Authenticator
  - Support for using existing PDO connections
  
## Requirements
  - [Zend Framework 2](http://www.github.com/zendframework/zf2)
  
## Doctrine CLI
The Doctrine CLI has been pre-configured and is available in DoctrineModule\bin. It should work as
is without any special configuration required for MongoODM and ORM.

## Installation
See the [INSTALL.md](http://www.github.com/doctrine/DoctrineModule/tree/master/docs/INSTALL.md) file.

## Upgrading
See the [UPGRADE.md](http://www.github.com/doctrine/DoctrineModule/tree/master/docs/UPGRADE.md) file.

## TODO
See the [TODO.md](http://www.github.com/doctrine/DoctrineModule/tree/master/docs/TODO.md) file.