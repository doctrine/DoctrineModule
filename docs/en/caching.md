# Caching

DoctrineModule provides bridging between
[`Laminas\Cache`](https://github.com/laminas/laminas-cache)
and [`Doctrine\Common\Cache`](https://github.com/doctrine/common/tree/master/lib/Doctrine/Common/Cache).
This may be useful in case you want to share configured cache instances across doctrine, symfony
and laminas projects.

You may use `Laminas\Cache` within your doctrine-related projects as following:

```php
$laminasCache = new \Laminas\Cache\Storage\Adapter\Memory(); // any storage adapter is OK here
$doctrineCache = new \DoctrineModule\Cache\LaminasStorageCache($laminasCache);
// now use $doctrineCache as a normal Doctrine\Common\Cache\Cache instance
```

You may use `Doctrine\Common\Cache` within your Laminas projects as following:

```php
$doctrineCache = new \Doctrine\Common\Cache\ArrayCache(); // any doctrine cache is OK here
$adapterOptions = new \Laminas\Cache\Storage\Adapter\AdapterOptions();
$laminasCacheStorage = new \DoctrineModule\Cache\DoctrineCacheStorage($adapterOptions, $doctrineCache);
// now use $laminasCacheStorage as a normal Laminas\Cache\Storage\StorageInterface instance.
```
