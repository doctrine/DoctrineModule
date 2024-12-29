Caching
=======

DoctrineModule provides some pre-configured caches using
`Laminas\Cache <https://github.com/laminas/laminas-cache>`__, which can be utilized
by either Doctrine ORM or Doctrine ODM.

The following caches are available by default:

In-Memory
~~~~~~~~~

Provided by ``laminas/laminas-cache-storage-adapter-memory``, you can pull this cache from
the container under the key ``doctrine.cache.array``. It does not really do any caching and
suits merely as a proof of concept or for cases, where you do not want to have caching.

Filesystem
~~~~~~~~~~

Provided by ``laminas/laminas-cache-storage-adapter-filesystem``, you can pull this cache from
the container under the key ``doctrine.cache.filesystem``. To override the location for the
cache storage folder, use the following configuration:

.. code:: php
    return [
        'caches' => [
            'doctrinemodule.cache.filesystem' => [
                'options' => [
                    'cache_dir' => './data/cache/',
                ],
            ],
        ],
    ];

APCu
~~~~

This cache requires the additional package ``laminas/laminas-cache-storage-adapter-apcu``, which
is not installed by default.

You can pull the cache from the container using the key ``doctrine.cache.apcu``. To pass additional
arguments for configuration, use the following config:

.. code:: php
    return [
        'caches' => [
            'doctrinemodule.cache.apcu' => [
                'options' => [

                ],
            ],
        ],
    ];

Memcached
~~~~~~~~~

This cache requires the additional package ``laminas/laminas-cache-storage-adapter-memcached``, which
is not installed by default.

You can pull the cache from the container using the key ``doctrine.cache.memcached``. To pass additional
arguments for configuration, use the following config:

.. code:: php
    return [
        'caches' => [
            'doctrinemodule.cache.memcached' => [
                'options' => [
                    'servers' => [

                    ],
                ],
            ],
        ],
    ];

Redis
~~~~~~~~~

This cache requires the additional package ``laminas/laminas-cache-storage-adapter-redis``, which
is not installed by default.

You can pull the cache from the container using the key ``doctrine.cache.redis``. The default config
will access a Redis server at localhost, port 6379. To pass additional arguments for configuration,
or to change the default config, use the following config:

.. code:: php
    return [
        'caches' => [
            'doctrinemodule.cache.redis' => [
                'options' => [
                    'server' => [
                        'host' => 'localhost',
                        'post' => 6379,
                    ],
                ],
            ],
        ],
    ];
