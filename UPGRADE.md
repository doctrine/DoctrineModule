# 0.9.0

 * Fixed intend in hydrator.md [#471](https://github.com/doctrine/DoctrineModule/pull/471)
 * Allow symfony 3.0 [#477](https://github.com/doctrine/DoctrineModule/pull/477)
 * Removed Travis build for 5.3 and added builds for 5.6 and 7.0 [#491](https://github.com/doctrine/DoctrineModule/pull/491)
 * Fixed documentation for Example 4 [#486](https://github.com/doctrine/DoctrineModule/pull/486)
 * Update year of license [#488](https://github.com/doctrine/DoctrineModule/pull/488)
 * Standardize array configuration [#489](https://github.com/doctrine/DoctrineModule/pull/489)
 * Fix #467 bypass value to validate message [#479](https://github.com/doctrine/DoctrineModule/pull/479)
 * Adding doctrine/coding-standard to builds [#478](https://github.com/doctrine/DoctrineModule/pull/478)
 * update hydrator s documentation url to current [#493](https://github.com/doctrine/DoctrineModule/pull/493)
 * Added PredisCache support [#492](https://github.com/doctrine/DoctrineModule/pull/492)
 * adding functionality to support custom attributes on value-options [#446](https://github.com/doctrine/DoctrineModule/pull/446)
 * FormElement s options can be set individual [#452](https://github.com/doctrine/DoctrineModule/pull/452)
 * Update module.config.php [#498](https://github.com/doctrine/DoctrineModule/pull/498)
 * Implemented Optgroup functionality within Proxy element [#502](https://github.com/doctrine/DoctrineModule/pull/502)
 * Removed check to allow zero-identifiers. [#459](https://github.com/doctrine/DoctrineModule/pull/459)
 * Refactored tests for option_attributes [#505](https://github.com/doctrine/DoctrineModule/pull/505)
 * Bump phpunit minimum version [#512](https://github.com/doctrine/DoctrineModule/pull/512)

# 0.8.1

 * Minimum PHP version has been bumped to `5.3.23` [#376](https://github.com/doctrine/DoctrineModule/pull/376)
 * Minimum `zendframework/zendframework` version has been bumped to `2.3` [#376](https://github.com/doctrine/DoctrineModule/pull/376)

# 0.8.0

 * Dependency to zendframework has been bumped from `2.*` to `~2.2`
 * Dependency to doctrine/common has been bumped from `>=2.3-dev,<2.5-dev` to `>=2.4,<2.6-dev`
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
