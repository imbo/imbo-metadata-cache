# Imbo metadata cache
This is an event listener that can be added to Imbo to cache metadata using one of the supported adapters. Currently adapters for Memcached and APCu exists.

[![Current build Status](https://secure.travis-ci.org/imbo/imbo-metadata-cache.png)](http://travis-ci.org/imbo/imbo-metadata-cache)

## Installation
Install using [Composer](http://getcomposer.org) by adding `imbo/imbo-metadata-cache` to you `composer.json` file:

```json
"require": {
    "imbo/imbo-metadata-cache": "^1.0.0"
}
```

## Configuration
To enable the metadata-cache in your Imbo installation you need to add a key to the `eventListener` part of the configuration:

```php
<?php
use Imbo\Plugin\MetadataCache\Cache,
    Imbo\Plugin\MetadataCache\EventListener;

return [
    // ...

    'eventListeners' => [
        // ...

        'metadataCache' => function() {
            $memcached = new Memcached();
            $memcached->addServer('localhost', 11211);
            $adapter = new Cache\Memcached($memcached, 'myCacheKeyNamespace');

            // or

            $adapter = new Cache\APCu('myCacheKeyNamespace');

            return new EventListener(['cache' => $adapter]);
        },

        // ...
    ],

    // ...
];
```

This plugin ships with two different adapters as shown in the example above, APCu and Memcached. APCu requires the [apcu pecl extension](http://pecl.php.net/package/apcu), and Memcached requires the [memcached pecl extension](http://pecl.php.net/package/memcached) and one or more running memcached servers.

### APCu
This adapter takes one optional parameter, `string $namespace`, that is used for namespacing of cache keys.

### Memcached
This adapter takes two parameters, the first being an instance of the [Memcached](http://php.net/manual/en/class.memcached.php) class and the second, `string $namespace`, that is used for namespacing of cache keys.
