Caching
=======

DoctrineModule provides bridging between
```Zend\Cache`` <https://github.com/zendframework/zf2/tree/master/library/Zend/Cache>`__
and
```Doctrine\Common\Cache`` <https://github.com/doctrine/common/tree/master/lib/Doctrine/Common/Cache>`__.
This may be useful in case you want to share configured cache instances
across doctrine, symfony and zendframework projects.

You may use ``Zend\Cache`` within your doctrine-related projects as
following:

.. code:: php

    $zendCache = new \Zend\Cache\Storage\Adapter\Memory(); // any storage adapter is OK here
    $doctrineCache = new \DoctrineModule\Cache\ZendStorageCache($zendCache);
    // now use $doctrineCache as a normal Doctrine\Common\Cache\Cache instance

You may use ``Doctrine\Common\Cache`` within your zendframework projects
as following:

.. code:: php

    $doctrineCache = new \Doctrine\Common\Cache\ArrayCache(); // any doctrine cache is OK here
    $adapterOptions = new \Zend\Cache\Storage\Adapter\AdapterOptions();
    $zendCacheStorage = new \DoctrineModule\Cache\DoctrineCacheStorage($adapterOptions, $doctrineCache);
    // now use $zendCacheStorage as a normal Zend\Cache\Storage\StorageInterface instance.

