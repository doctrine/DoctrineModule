# SpiffyDoctrine Module for Zend Framework 2

The SpiffyDoctrine module intends to integrate Doctrine 2 with the Zend Framework quickly and easily. 
The following features are intended to work out of the box: 

  - Configuration and creation of multiple entity managers, cache instance, connections, and event managers.
  - Specifying separate cache instances for metadata, query, and result caches.
  - Using a SQL Logger.
  - Configuration of annotations via registry files and/or namespaces (such as Gedmo DoctrineExtensions).

## Installation

The simplest way to install is to clone the repository into your /modules directory add the 
SpiffyDoctrine key to your modules array.

  1. cd my/project/folder
  2. git clone https://SpiffyJr@github.com/SpiffyJr/SpiffyDoctrine.git modules/SpiffyDoctrine --recursive
  3. open my/project/folder/configs/application.config.php and add 'SpiffyDoctrine' to your 'modules' parameter.
  4. Alter the configuration (most likely your connection and entities path(s)) by adding the required changes to 
     my/project/folder/modules/Application/module.config.php.

## Example standard configuration

    // modules/Application/module.config.php
    'di' => array(
        'instance' => array(
            'doctrine-container' => array(
                'parameters' => array(
                    'connection' => array(
                        'default' => array(
                            'evm' => 'default',
                            'dbname' => 'mydb',
                            'user' => 'root',
                            'password' => '',
                            'host' => 'localhost',
                            'driver' => 'pdo_mysql'
                        )
                    ),
                    'em' => array(
                        'default' => array(
                            'driver' => array(
                                'paths' => array(
                                    '/path/to/your/entities',
                                ),
                            )
                        )
                    )
                )
            )
        )
    )


## Usage

### Accessing the default, pre-configured, entity-manager instance
A default EntityManager instance has been configured for you and is called em-default. You can access
it from an ActionController using the locator as follows:

    $em = $this->getLocator()->get('em-default');
    
If for some reason you want access to additional objects such as the EVM, Cache, or Connection instances
you can get them from the SpiffyDoctrine\Container\Container.

    $container = $this->getLocator()->get('doctrine-container');


## Doctrine CLI
The Doctrine CLI has been pre-configured and is available in SpiffyDoctrine\bin. It should work as
is without any special configuration required.
