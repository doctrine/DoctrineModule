# 1.0

 * Dependency to zendframework has been bumped from `~2.1` to `~2.2`
 * Class name changes:
       - `DoctrineModule\ServiceFactory\AbstractDoctrineServiceFactory` -> `DoctrineModule\ServiceFactory\DoctrineServiceAbstractFactory`
       - `DoctrineModule\Authentication\Adapter\ObjectRepository` -> `DoctrineModule\Authentication\Adapter\ObjectRepositoryAdapter`
       - `DoctrineModule\Authentication\Storage\ObjectRepository` -> `DoctrineModule\Authentication\Storage\ObjectRepositoryStorage`
       - `DoctrineModule\Paginator\Adatper\Collection` -> `DoctrineModule\Paginator\Adatper\CollectionAdapter`
       - `DoctrineModule\Paginator\Adatper\Selectable` -> ``DoctrineModule\Paginator\Adatper\SelectableAdater`
 * Configuration has changed significantly. Most services are created by `DoctrineModule\ServiceFactory\DoctrineServiceAbstractFactory`,
   and expects configuration to follow this pattern:
       - All service names should start with `doctrine.`.
       - All service names should use `.` to delimit words in the service name
       - All service names should survive `Zend\ServiceManager\ServiceManager`'s cannonacalization process unchanged.
         If they do not, getting the service directly will work, but aliases will not. In practice, this means service names should be all lower case,
         and should not include the characters `_-\ /`.
       - If a service with name `doctrine.foo.bar.baz` is requested, then `DoctrineServiceAbstractFactory` will get the service
        called `doctrine.factory.foo.bar` from the ServiceManager. The `doctrine.factory.foo.bar` instance must be an object implementing
        `DoctrineModule\Factory\AbstractFactoryInterface`. `$instnace::create($options)` will be called to create the oringally
        requested `doctrine.foo.bar.baz` service. The `$options` passed to `create` will be an array taken from
        the application config: `$config['doctrine']['foo']['bar']['baz']`s.
       - For example in the config options for the default EventManager should be placed in
         `$config['doctrine']['eventmanager']['default']`. The EventManager will be created by a
         `DoctrineModule\Factory\EventManagerFactory` which is fetched from the ServiceManager with the service
         name `doctrine.factory.eventmanager`. To get a configured instance of the default EventManager call
         `$serviceManager->get('doctrine.eventmanager.default')`.
 * Configuration for authentication services has been rearranged to follow the pattern above, with separate
   config keys for `adapter`, `storage`, and `service`. See `module.config.php`.
 * Authentication configuration no longer supports setting `objectRepository`. You must set both `objectManager` and
   `identityClass`. This significantly simplifies the code, and allows a flat config for easy caching.
 * Most of the factories that were in `DoctrineModule\Service` have been moved to `DoctrineModule\Factory`. This is because
   they are not actual service factories to be consumed by the ServiceManager. Rather they are consumed by `DoctrineServiceAbstractFactory`.
 * When configuring drivers, the cache key must now be a full service name. eg `doctrine.cache.array`.
 * When configuring a driver chain, the `$options->drivers` array may contain driver instances, or complete service names. eg:


    `'driver' => array(
         'default' => array(
             'drivers' => array(
                 'My\Namespace' => 'doctrine.driver.mydriver'
             ),
         ),
         'mydriver' => array(
             'class' => 'Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver',
             'paths' => array('path/to/my/namespace')
         ),
     ),`

# 0.8.0

 * Dependency to zendframework has been bumped from `2.*` to `~2.1`
 * Dependency to doctrine/common has been bumped from `>=2.3-dev,<2.5-dev` to `>=2.3,<2.5-dev`
 * It is now possible to define a callable for option `label_generator` in `DoctrineModule\Form\Element\Proxy`
   as of [#219](https://github.com/doctrine/DoctrineModule/pull/219)
 * `DoctrineModule\Authentication\Adapter\ObjectRepository` now inherits logic from
   `Zend\Authentication\Adapter\AbstractAdapter` as of [#156](https://github.com/doctrine/DoctrineModule/pull/156).
   Methods `setIdentityValue`, `getIdentityValue`, `setCredentialValue`, `getCredentialValue` are now deprecated.
 * It is now possible to set the cache namespace in the cache configuration as
   of [#164](https://github.com/doctrine/DoctrineModule/pull/164)
 * All services named with the `doctrine.<something>.<name>` pattern are now handled by
   `DoctrineModule\ServiceFactory\AbstractDoctrineServiceFactory`, which simplifies the instantiation
   logic for different occurrences of `<name>` as of
   [#226](https://github.com/doctrine/DoctrineModule/pull/226) and
   [#76](https://github.com/doctrine/DoctrineModule/pull/76)
 * The CLI tools are now also available as a standard ZF2 console as of
   [#226](https://github.com/doctrine/DoctrineModule/pull/226),
   [#200](https://github.com/doctrine/DoctrineModule/pull/200) and
   [#137](https://github.com/doctrine/DoctrineModule/pull/137). From now on, you can simply run
   `php ./public/index.php` in a standard zf2 skeleton application and the tools will be available
   in there. The console in `./vendor/bin/doctrine-module` is now deprecated.
 * The module does not implement `Zend\ModuleManager\Feature\AutoloaderProviderInterface` anymore.
   Please use [composer](http://getcomposer.org/) autoloading or setup autoloading yourself.
 * Service `doctrine.cache.zendcachestorage` was removed from the pre-configured services as of
   [#226](https://github.com/doctrine/DoctrineModule/pull/226).
 * Instantiating a `DoctrineModule\Stdlib\Hydrator\DoctrineObject` does not require a
   `targetClass` anymore. This means you have to modify the way you create hydrator
   by replacing this: `$hydrator = new Hydrator($objectManager, 'Application\Entity\User', true)` by
   `$hydrator = new Hydrator($objectManager, true)`
