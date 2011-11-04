# SpiffyDoctrine Module for Zend Framework 2
The SpiffyDoctrine module intends to integrate Doctrine 2 ORM with Zend Framework 2 quickly and easily. The following features are intended to work out of the box: 
  
  - Multiple ORM entity managers
  - Multiple DBAL connections
  - Caches for metadata, queries and resultsets
  - Using a SQL logger
  - Custom dql functions, additional hydration modes
  - Named DQL and native queries
  - Multiple metadata drivers
  - Annotations registries initialization (such as Gedmo DoctrineExtensions).
  - Validators for EntityExists and NoEntityExists.
  - Authentication adapter for Zend\Authenticator.
  - Support for using existing PDO connections.
  
## Requirements
  - Zend Framework 2

## Available locator items
Following locator items are preconfigured with this module:

  - doctrine, a SpiffyDoctrine\Service\Doctrine instance

## Doctrine CLI
The Doctrine CLI has been pre-configured and is available in SpiffyDoctrine\bin. It should work as
is without any special configuration required.
