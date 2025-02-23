<?php declare(strict_types=1);
namespace Imbo\Plugin\MetadataCache\Cache;

use Memcached as PeclMemcached;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Memcached::class)]
class MemcachedTest extends CacheTests
{
    protected function getAdapter(): Memcached
    {
        if (!extension_loaded('memcached')) {
            $this->markTestSkipped('Memcached is not installed');
        }

        $host = (string) getenv('MEMCACHED_HOST');
        $port = (int) getenv('MEMCACHED_PORT');

        if ('' === $host || 0 === $port) {
            $this->markTestSkipped('Specify both MEMCACHED_HOST and MEMCACHED_PORT in your PHPUnit configuration file to run this test case');
        }

        $memcached = new PeclMemcached();
        $memcached->addServer($host, $port);

        return new Memcached($memcached, uniqid('imbo-metadata-cache-test-', true));
    }
}
