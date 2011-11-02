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
  
## Requirements
  - Zend Framework 2

## Installation
The simplest way to install is to clone the repository into your /modules directory add the 
SpiffyDoctrine key to your modules array before your Application module key.

  1. cd my/project/folder
  2. git clone https://SpiffyJr@github.com/SpiffyJr/SpiffyDoctrine.git modules/SpiffyDoctrine --recursive
  3. open my/project/folder/configs/application.config.php and add 'SpiffyDoctrine' to your 'modules' parameter.
  4. Alter the configuration (most likely your connection and entities path(s)) by adding the required changes to 
     my/project/folder/modules/Application/module.config.php.

## Example standard configuration
    // modules/Application/module.config.php
    'di' => array(
        'instance' => array(
            'spiffy-connection' => array(
                'parameters' => array(
                    'params' => array(
                        'driver'   => 'pdo_mysql',
                        'host'     => 'localhost',
                        'port'     => '3306', 
                        'user'     => 'YOUR_USER',
                        'password' => 'YOUR_PASSWORD',
                        'dbname'   => 'YOUR_DB_NAME',
                    ),
                ),
            ),
            'spiffy-configuration' => array(
                'parameters' => array(
                    'dir' => '/path/where/to/generate/proxies',
                ),
            ),
            'spiffy-annotationdriver' => array(
                'parameters' => array(
                    'paths' => array(
                        '/path/to/Entities',
                        '/path/to/other/Entities',
                    ),
                ),
            ),
        ),
    );


## Usage

### Accessing the default, pre-configured, entity-manager instance
A default EntityManager instance has been configured for you and is called "spiffy-entitymanager". You can access
it from an ActionController using the locator as follows:

    $em = $this->getLocator()->get('spiffy-entitymanager');
    
If for some reason you want access to additional objects such as the EventManager, Cache, or Connection instances
you can get them from the locator the same way.

## Available locator items
Following locator items are preconfigured with this module:

  - 'spiffy-connection', a Doctrine\DBAL\Connection instance
  - 'spiffy-configuration, a SpiffyDoctrine\ORM\Configuration instance
  - 'spiffy-metadatacache, a Doctrine\Common\Cache\ArrayCache instance
  - 'spiffy-querycache, a Doctrine\Common\Cache\ArrayCache instance
  - 'spiffy-resultcache, a Doctrine\Common\Cache\ArrayCache instance
  - 'spiffy-eventmanager, a Doctrine\Common\EventManager instance
  - 'spiffy-metadatadriver, a SpiffyDoctrine\ORM\Mapping\Driver\DriverChain instance
  - 'spiffy-annotationdriver, a Doctrine\ORM\Mapping\Driver\AnnotationDriver instance
  - 'spiffy-cachedreader, a Doctrine\Common\Annotations\CachedReader instance
  - 'spiffy-annotationcache, a Doctrine\Common\Cache\ArrayCache instance
  - 'spiffy-indexedreader, a Doctrine\Common\Annotations\IndexedReader instance
  - 'spiffy-annotationreader, a Doctrine\Common\Annotations\AnnotationReader instance

## Doctrine CLI
The Doctrine CLI has been pre-configured and is available in SpiffyDoctrine\bin. It should work as
is without any special configuration required.

## EntityExists and NoEntityExists Validators
The EntityExists and NoEntityExists are validators similar to Zend\Validator\Db validators. You can 
pass a variety of options to determine validity. The most basic use case requires an entity manager (em),
an entity, and a field. You also have the option of specifying a query_builder Closure to use if you
want to fine tune the results. 

    $validator = new \SpiffyDoctrine\Validator\NoEntityExists(array(
       'em' => $this->getLocator()->get('spiffy-entitymanager'),
       'entity' => 'SpiffyUser\Entity\User',
       'field' => 'username',
       'query_builder' => function($er) {
           return $er->createQueryBuilder('q');
       }
    ));
    var_dump($validator->isValid('test'));

## Tuning for production
Tuning the system for production should be as simple as setting the following in your
configuration (example presumes you have APC installed).

    'di' => array(
        'instance' => array(
            'alias' => array(
                'spiffy-metadatacache'      => 'Doctrine\Common\Cache\ApcCache',
                'spiffy-querycache'         => 'Doctrine\Common\Cache\ApcCache',
                'spiffy-resultcache'        => 'Doctrine\Common\Cache\ApcCache',
                'spiffy-annotationcache'    => 'Doctrine\Common\Cache\ApcCache',
            ),
            'spiffy-configuration' => array(
                'parameters' => array(
                    'autoGenerateProxies' => false,
                ),
            ),
        ),
    );
