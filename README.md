# Imbo metadata cache

[![CI](https://github.com/imbo/imbo-metadata-cache/workflows/CI/badge.svg)](https://github.com/imbo/imbo-metadata-cache/actions?query=workflow%3ACI)

This is an event listener that can be added to Imbo to cache metadata using one of the supported adapters. The event listener currently supports [Memcached](https://memcached.org/) and [APC User Cache](https://www.php.net/manual/en/book.apcu.php).

## Installation

    composer require imbo/imbo-metadata-cache

## Usage

To enable the metadata cache in your Imbo installation you need to add a key to the `eventListener` part of the configuration:

```php
<?php declare(strict_types=1);
use Imbo\Plugin\MetadataCache\Cache;
use Imbo\Plugin\MetadataCache\EventListener;

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

This plugin ships with two different adapters as shown in the example above, APCu and Memcached. APCu requires the [apcu pecl extension](https://pecl.php.net/package/apcu), and Memcached requires the [memcached pecl extension](https://pecl.php.net/package/memcached) and one or more running memcached servers.

## Running integration tests

If you want to run the integration tests you will need a running Memcached service. The repo contains a simple configuration file for [Docker Compose](https://docs.docker.com/compose/) that you can use to quickly run a Memcached instance.

If you wish to use this, run the following command to start up the service after you have cloned the repo:

    docker-compose up -d

After the service is running you can execute all tests by simply running PHPUnit:

    composer run test # or ./vendor/bin/phpunit

## License

MIT, see [LICENSE](LICENSE).
