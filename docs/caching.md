# Caching

DoctrineModule provides bridging between 
[`Zend\Cache`](https://github.com/zendframework/zf2/tree/master/library/Zend/Cache)
and [`Doctrine\Common\Cache`](https://github.com/doctrine/common/tree/master/lib/Doctrine/Common/Cache).
This may be useful in case you want to share configured cache instances across doctrine, symfony
and zendframework projects.

You may use `Zend\Cache` within your doctrine-related projects as following:

```php
$zendCache = new \Zend\Cache\Storage\Adapter\Memory(); // any storage adapter is OK here
$doctrineCache = new \DoctrineModule\Cache\ZendStorageCache($zendCache);
// now use $doctrineCache as a normal Doctrine\Common\Cache\Cache instance
```

You may use `Doctrine\Common\Cache` within your zendframework projects as following:

```php
$doctrineCache = new \Doctrine\Common\Cache\ArrayCache(); // any doctrine cache is OK here
$adapterOptions = new \Zend\Cache\Storage\Adapter\AdapterOptions();
$zendCacheStorage = new \DoctrineModule\Cache\DoctrineCacheStorageTest($adapterOptions, $zendCache);
// now use $zendCacheStorage as a normal Zend\Cache\Storage\StorageInterface instance.
```


### Caching queries, results and metadata

If you want to set a cache for query, result and metadata, you can specify this inside your `module.config.php`

```php
'doctrine' => array(
    'configuration' => array(
        'orm_default' => array(
            'query_cache'       => 'apc',
            'result_cache'      => 'apc',
            'metadata_cache'    => 'apc'
        )
    )
),
```

The previous configuration take in consideration an Apc adapter. You can specify any other adapter that implements the `Doctrine\Common\Cache\Cache` interface.

Example with Memcached

```php
'doctrine' => array(
    'configuration' => array(
        'orm_default' => array(
            'query_cache'       => 'memcached',
            'result_cache'      => 'memcached',
            'metadata_cache'    => 'memcached'
        )
    )
),
```

In this case you have to specify a custom factory in your service_manager configuration

```php
// module.config.php
'service_manager' => array(
    'factories' => array(
        'my_memcached_alias' => function() {
            $memcached = new \Memcached();
            $memcached->addServer('localhost', 11211);
            return $memcached;
        },
    ),
),
```