# 1.0

 * Dependency to zendframework has been bumped from `~2.1` to `~2.2`
 * `DoctrineModule\ServiceFactory\AbstractDoctrineServiceFactory` has changed name to
   `DoctrineModule\ServiceFactory\DoctrineServiceAbstractFactory` to make clear that it is not an abstract class.
 * Configuration has changed significantly. Most services are created by `DoctrineModule\ServiceFactory\DoctrineServiceAbstractFactory`,
   and expects configuration to follow this pattern:
       - All service names should start with `doctrine.`.
       - All service names should use `.` to delimit words in name
       - All service names should survive `Zend\ServiceManager\ServiceManager`'s cannonacalization process.
         If they do not, aliases will not work. In practice, this means service names should be all lower case,
         and should not include the characters `_-\`.
       - If a service with name `doctrine.foo.bar.baz` is requested, then the service called `doctrine.foo.bar` will
         be fetched from the ServiceManager. The `doctrine.foo.bar` instance must be an object implementing
        `DoctrineModule\Factory\AbstractFactory`. `$instnace::create($options)` will be called to create the oringally
        requested `doctrine.foo.bar.baz` service. The `$options` passed to `create` will be an array taken from
        the application config: `$config['doctrine']['foo']['bar']['baz']`s.
       - For example in the ORM module, `$config['doctrine']['connection']['orm_default']` is moved
         to `$config['doctrine']['orm']['connection']['default']`. And, aquiring the EntityManager from the ServiceManager changes
         from `doctrine.entitymanager.orm_default` to `doctrine.orm.entitymanager.default`.
 * Configuration for authentication services has been rearranged to follow the pattern above, with separate
   config keys for `adapter`, `storage`, and `service`.
 * Authentication configuration no longer supports setting `objectRepository`. You must set both `objectManager` and
   `identityClass`. This significantly simplifies the code, and allows a flat config for easy caching.
 * Most of the factories that were in `DoctrineModule\Service` have been moved to `DoctrineModule\Factory`. This is because
   they are not actual service factories to be consumed by the ServiceManager. Rather they are consumed by DoctrineServiceAbstractFactory.
 * When configuring drivers, the cache key must now be a full service name. eg `doctrine.cache.array`.
 * When configuring a driver chain, the `$options->drivers` array may contain driver instances, or complete service names.

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
