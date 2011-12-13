# Installing the SpiffyDoctrine module for Zend Framework 2 
The simplest way to install is to clone the repository into your /vendor directory add the 
SpiffyDoctrine key to your modules array before your Application module key.

  1. cd my/project/folder
  2. git clone git://github.com/SpiffyJr/SpiffyDoctrine.git vendor/SpiffyDoctrine --recursive
  3. open my/project/folder/configs/application.config.php and add 'SpiffyDoctrine' to your 'modules' parameter.
  4. Alter the configuration (most likely your connection and entities path(s)) by adding the required changes to 
     my/project/folder/module/Application/module.config.php.
     

## Example standard configuration
    // module/Application/module.config.php
    'di' => array(
        'instance' => array(
            'doctrine_connection' => array(
                'parameters' => array(
                    'params' => array(
                        'driver'   => 'pdo_mysql',
                        'port'     => '3306', 
                        'host'     => 'DB_HOST',
                        'user'     => 'DB_USERNAME',
                        'password' => 'DB_PASSWORD',
                        'dbname'   => 'DB_NAME',
                    ),
                )
            ),
            'doctrine_driver_chain' => array(
                'parameters' => array(
                    'drivers' => array(
                        'application_annotation_driver' => array(
                            'class'           => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                            'namespace'       => 'My\Entity\Namespace',
                            'paths'           => array(__DIR__ . '/../src/Application/My/Entity/Folder'),
                        ),
                    )
                )
            ),
        )
    )
    
## Usage
Access the entity manager using the following locator: 

    $em = $this->getLocator()->get('doctrine_em');
    
## Tuning for production
Tuning the system for production should be as simple as setting the following in your
configuration (example presumes you have APC installed).

    'di' => array(
        'instance' => array(
            'doctrine_config' => array(
                'parameters' => array(
                    'opts' => array(
                        'auto_generate_proxies' => false,
                        'metadata_driver_impl' => array(
                            'doctrine_annotationdriver' => array(
                                'cache_class' => 'Doctrine\Common\Cache\ApcCache'
                            )
                        ),
                        'metadata_cache_impl' => 'Doctrine\Common\Cache\ApcCache',
                        'query_cache_impl'    => 'Doctrine\Common\Cache\ApcCache',
                        'result_cache_impl'   => 'Doctrine\Common\Cache\ApcCache'
                    ),
                ),
            ),
        ),
        
## Using a Zend\Di configured PDO instance or pre-existing PDO instance
Using a PDO connection requires a minor modification to your configuration. Simply add the 'pdo' 
instance and remove the 'conn' option. If you are not using Zend\Di you can also pass the 'pdo'
key to the 'conn' array.

    'di' => array( 
        'instance' => array(
            'doctrine_config' => array(
                'parameters' => array(
                    'opts' => array(
                        'auto_generate_proxies' => true
                    ),
                    'metadataCache' => 'doctrine_cache_apc',
                    'queryCache'    => 'doctrine_cache_apc',
                    'resultCache'   => 'doctrine_cache_apc', // optional
                )
            ),
            'doctrine_driver_chain' => array(
                'parameters' => array(
                    'cache' => 'doctrine_cache_apc'
                )
            )
        )
    )
