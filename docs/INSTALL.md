# Installing the SpiffyDoctrine module for Zend Framework 2 
The simplest way to install is to clone the repository into your /modules/vendor directory add the 
SpiffyDoctrine key to your modules array before your Application module key.

  1. cd my/project/folder
  2. git clone git://github.com/SpiffyJr/SpiffyDoctrine.git modules/vendor/SpiffyDoctrine --recursive
  3. open my/project/folder/configs/application.config.php and add 'SpiffyDoctrine' to your 'modules' parameter.
  4. Alter the configuration (most likely your connection and entities path(s)) by adding the required changes to 
     my/project/folder/modules/Application/module.config.php.
     

## Example standard configuration
    // modules/Application/module.config.php
    'di' => array(
        'instance' => array(
            'doctrine' => array(
                'parameters' => array(
                    'conn' => array(
                        'driver'   => 'pdo_mysql',
                        'host'     => 'localhost',
                        'port'     => '3306', 
                        'user'     => 'USERNAME',
                        'password' => 'PASSWORD',
                        'dbname'   => 'DBNAME',
                    ),
                    'config' => array(
                        'metadata_driver_impl' => array(
                            // to add multiple drivers just follow the format below and give them a different keyed name
                            // cache_class is only required for annotation drivers
                            'application_annotation_driver' => array(
                                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                                'namespace' => 'My\Entity\Namespace',
                                'paths' => array('/some/path/to/entities'),
                                'cache_class' => 'Doctrine\Common\Cache\ArrayCache',
                            )
                        )
                    )
                ),
            ),
        )
    )
    
## Usage
Access the entity manager using the following locator: 

    $em = $this->getLocator()->get('doctrine')->getEntityManager();
    
## Tuning for production
Tuning the system for production should be as simple as setting the following in your
configuration (example presumes you have APC installed).

    'di' => array(
        'instance' => array(
            'doctrine' => array(
                'parameters' => array(
                    'config' => array(
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
            'doctrine' => array(
                'parameters' => array(
                     'conn' => array(
                    //     'driver'   => 'pdo_mysql',
                    //     'host'     => 'localhost',
                    //     'port'     => '3306', 
                    //     'user'     => 'USERNAME',
                    //     'password' => 'PASSWORD',
                    //     'dbname'   => 'DBNAME',
                        'pdo' => 'my_pdo_object' // only available if not using Zend\Di
                    ),
                    'config' => array(
                        'metadata_driver_impl' => array(
                            'doctrine_annotationdriver' => array(
                                'namespace' => 'My\Entity\Namespace',
                                'paths'     => array('path/to/your/entities')
                            )
                        )
                    ),
                    'pdo' => 'my_pdo_alias'
                ),
            ),
        )
    )
