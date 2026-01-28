<?php declare(strict_types=1);

namespace Imbo\Plugin\MetadataCache\Cache;

use Memcached as PeclMemcached;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RequiresEnvironmentVariable;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

#[CoversClass(Memcached::class)]
#[RequiresPhpExtension('memcached')]
#[RequiresEnvironmentVariable('MEMCACHED_HOST')]
#[RequiresEnvironmentVariable('MEMCACHED_PORT')]
class MemcachedTest extends CacheTests
{
    protected function getAdapter(): Memcached
    {
        $host = (string) getenv('MEMCACHED_HOST');
        $port = (int) getenv('MEMCACHED_PORT');

        $memcached = new PeclMemcached();
        $memcached->addServer($host, $port);

        /** @var array<mixed>|false */
        $version = $memcached->getVersion();

        if (false === $version) {
            $this->markTestSkipped('Memcached server is not available.');
        }

        return new Memcached($memcached, uniqid('imbo-metadata-cache-test-', true));
    }
}
