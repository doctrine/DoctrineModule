# 2.1.0
This is the first major release since 1.2.0 and brings this library up to date
with current practices and a slew of updates and fixes.
* updated PHP CodeSniffer to version 2.7
* added phpcs.xml with defined CS rules
* fixed code to pass all CS checks
* short array syntax in docs
* composer scripts to run unit and cs checks
* updated travis configuration to use composer scripts [#575](https://github.com/doctrine/DoctrineModule/pull/575)
* Updated DoctrineModule authentication doc with some ZF3-related information and examples [#582](https://github.com/doctrine/DoctrineModule/pull/582)
* expiredRead is now staticTtl in cache test [#594](https://github.com/doctrine/DoctrineModule/pull/594)
* Added ConfigProvider in line with ZF components [#590](https://github.com/doctrine/DoctrineModule/pull/590)
* corrected errors in sample code [#606](https://github.com/doctrine/DoctrineModule/pull/606)
* Migrate all module.config.php into ConfigProvider in line with ZF adaption
* Added more tests for keys and serialziation for ConfigProvider
* Authentication guide update for ZF3 [#599](https://github.com/doctrine/DoctrineModule/pull/599)
* Use service manager factory for cache when possible
* Use class name constants where possible; break even on the last case in a switch [#605](https://github.com/doctrine/DoctrineModule/pull/605)
* Add APCu cache to configuration [#589](https://github.com/doctrine/DoctrineModule/pull/589)
* Added factories for (No)ObjectExists and UniqueObject validators [#604](https://github.com/doctrine/DoctrineModule/pull/604)
* Drop PHP 5.x support
* Bump minimum PHP version to 7.1
* Drop HHVM from Travis build [#611](https://github.com/doctrine/DoctrineModule/pull/611)
* Allow symfony 4 [#612](https://github.com/doctrine/DoctrineModule/pull/612)
* Removed Version.php [#616](https://github.com/doctrine/DoctrineModule/pull/616)

* License headers [#617](https://github.com/doctrine/DoctrineModule/pull/617)
* Upgrade to PHPUnit ^7.0 [#615](https://github.com/doctrine/DoctrineModule/pull/615/commits)
* Extend DoctrineModule\Stdlib\Hydrator\DoctrineObject::handleTypeConversions() to handle all basic conversions according to documentation instead of just DateTime object related values [#626](https://github.com/doctrine/DoctrineModule/pull/626/commits)
* Use Inflector instead of ucfirst [#625](https://github.com/doctrine/DoctrineModule/pull/625/commits)
* Hydration strategies can be replaced after the hydrator is created [#627](https://github.com/doctrine/DoctrineModule/pull/627/commits)

# 1.2.0
* ZF3 compatibility [#567](https://github.com/pull/567)
* Expose to `zend*component*installer` as module `DoctrineModule` [#570](https://github.com/pull/570)
* ZF3 Composer dependencies * hotfix [#571](https://github.com/pull/571)
* Command line tools improvements [#572](https://github.com/pull/572)
* Add missing Doctrine APCu Cache [#569](https://github.com/pull/569)
* Changed isset for array_key_exists in context check [#568](https://github.com/pull/568)
* Hydrator ignores private/protected getter/setter [#560](https://github.com/pull/560)

# 1.1.0
 * fixed require-dev dependancies [#557](https://github.com/doctrine/DoctrineModule/pull/557)
 * Update hydrator.md [#561](https://github.com/doctrine/DoctrineModule/pull/561)
 * [git] Add .gitattributes to remove unneeded files [#559](https://github.com/doctrine/DoctrineModule/pull/559)
 * refactored factories for SM v3 [#558](https://github.com/doctrine/DoctrineModule/pull/558)

# 1.0.1

 * Drop compatibility with PHP 5.4 [#553](https://github.com/doctrine/DoctrineModule/pull/553)
 * Improve TravisCi build to work with PHP 7 [#553](https://github.com/doctrine/DoctrineModule/pull/553)
 * Update doctrine/common ~2.6 [#551](https://github.com/doctrine/DoctrineModule/pull/551)

# 1.0.0

 * Remove deprecated api call from test [#523](https://github.com/doctrine/DoctrineModule/pull/523)
 * Allow for the use of Zend\Cache\Service\StorageCacheAbstractServiceFactory [#547](https://github.com/doctrine/DoctrineModule/pull/547)

# 0.10.1

 * Drop compatibility with PHP 5.4 [#553](https://github.com/doctrine/DoctrineModule/pull/553)
 * Update doctrine/common ~2.6 [#551](https://github.com/doctrine/DoctrineModule/pull/551)

# 0.10.0

 * Fixed php_codesniffer dependency [#521](https://github.com/doctrine/DoctrineModule/pull/521)
 * Fixed wrong Predis Mock [#534](https://github.com/doctrine/DoctrineModule/pull/534)
 * Update hydrator.md [#537](https://github.com/doctrine/DoctrineModule/pull/537)
 * Fix for issue #230 and  fixes for #234 [#520](https://github.com/doctrine/DoctrineModule/pull/520)
 * Feature/snake case [#539](https://github.com/doctrine/DoctrineModule/pull/539)
 * Adds additional processing for DoctrineObject::toMany [#535](https://github.com/doctrine/DoctrineModule/pull/535)

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
