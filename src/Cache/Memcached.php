<?php declare(strict_types=1);
namespace Imbo\Plugin\MetadataCache\Cache;

use Memcached as PeclMemcached;

/**
 * Memcached cache adapter
 */
class Memcached implements CacheInterface
{
    private string $namespace;

    private PeclMemcached $memcached;

    /**
     * Class constructor
     *
     * @param PeclMemcached $memcached An instance of pecl/memcached
     * @param string $namespace A prefix that will be added to all keys
     */
    public function __construct(PeclMemcached $memcached, $namespace)
    {
        $this->memcached = $memcached;
        $this->namespace = $namespace;
    }

    public function get(string $key)
    {
        return $this->memcached->get($this->getKey($key));
    }

    public function set(string $key, $value, int $expire = 0): bool
    {
        return $this->memcached->set($this->getKey($key), $value, $expire);
    }

    public function delete(string $key): bool
    {
        return $this->memcached->delete($this->getKey($key));
    }

    /**
     * Generate a namespaced key
     *
     * @param string $key The key specified by the user
     * @return string A namespaced key
     */
    protected function getKey(string $key): string
    {
        return $this->namespace . ':' . $key;
    }
}
