<?php
namespace ImboTest\Cache;

use Imbo\Cache\Memcached,
    Memcached as PeclMemcached;

/**
 * @covers Imbo\Cache\Memcached
 */
class MemcachedTest extends CacheTests {
    protected function getDriver() {
        if (!extension_loaded('memcached')) {
            $this->markTestSkipped('Memcached is not installed');
        }

        $host = !empty($GLOBALS['MEMCACHED_HOST']) ? $GLOBALS['MEMCACHED_HOST'] : null;
        $port = !empty($GLOBALS['MEMCACHED_PORT']) ? $GLOBALS['MEMCACHED_PORT'] : null;

        if (!$host || !$port) {
            $this->markTestSkipped('Specify both MEMCACHED_HOST and MEMCACHED_PORT in your phpunit.xml file to run this test case');
        }

        $memcached = new PeclMemcached();
        $memcached->addServer($host, $port);

        static $timestamp = 0;

        if (!$timestamp) {
            $timestamp = microtime(true);
        }

        return new Memcached($memcached, 'ImboTestSuite' . $timestamp);
    }
}
