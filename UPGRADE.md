# 0.9.0

 * Minimum PHP version has been bumped to `5.3.23` [#376](https://github.com/doctrine/DoctrineModule/pull/376)
 * Minimum `zendframework/zendframework` version has been bumped to `2.3` [#376](https://github.com/doctrine/DoctrineModule/pull/376)

# 0.8.1

 * [#376](https://github.com/doctrine/DoctrineModule/pull/376) Bumping PHP and ZF2 dependencies, branch alias for master
 * [#378](https://github.com/doctrine/DoctrineModule/pull/378) PSR fixing.
 * [#381](https://github.com/doctrine/DoctrineModule/pull/381) Validator documentatin update
 * [#388](https://github.com/doctrine/DoctrineModule/pull/388) Added exception for missing required parameter for `find_method` option as
 * [#390](https://github.com/doctrine/DoctrineModule/pull/390) Clarified how to pass sort information.
 * [#395](https://github.com/doctrine/DoctrineModule/pull/395) Issue with objects being cast to array in validators
 * [#397](https://github.com/doctrine/DoctrineModule/pull/397) Enhancement: use exit code from run()
 * [#401](https://github.com/doctrine/DoctrineModule/pull/401) Reading Inconsistency
 * [#391](https://github.com/doctrine/DoctrineModule/pull/391) UniqueObject Validator * allowing composite identifiers from context or not
 * [#400](https://github.com/doctrine/DoctrineModule/pull/400) let zf2 console return exit status
 * [#404](https://github.com/doctrine/DoctrineModule/pull/404) Fix form elements
 * [#406](https://github.com/doctrine/DoctrineModule/pull/406) Fix context unique
 * [#421](https://github.com/doctrine/DoctrineModule/pull/421) Make DoctrineObject use AbstractHydrator s namingStrategy
 * [#426](https://github.com/doctrine/DoctrineModule/pull/426) update year in license
 * [#436](https://github.com/doctrine/DoctrineModule/pull/436) Fixing typo and updating paginator link to ZF 2.3
 * [#450](https://github.com/doctrine/DoctrineModule/pull/450) minor cs fix
 * [#458](https://github.com/doctrine/DoctrineModule/pull/458) Update doctrine*module.php
 * [#462](https://github.com/doctrine/DoctrineModule/pull/462) Adding custom Doctrine*Cli Commands
 * [#465](https://github.com/doctrine/DoctrineModule/pull/465) Re*enable scrutinizer*ci code coverage
 * [#453](https://github.com/doctrine/DoctrineModule/pull/453) phpdoc fixes

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
