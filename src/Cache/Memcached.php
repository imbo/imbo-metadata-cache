<?php
namespace Imbo\Plugin\MetadataCache\Cache;

use Memcached as PeclMemcached;

/**
 * Memcached cache adapter
 *
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class Memcached implements CacheInterface {
    /**
     * Key namespace
     *
     * @var string
     */
    private $namespace;

    /**
     * The memcached instance to use
     *
     * @var PeclMemcached
     */
    private $memcached;

    /**
     * Class constructor
     *
     * @param PeclMemcached $memcached An instance of pecl/memcached
     * @param string $namespace A prefix that will be added to all keys
     */
    public function __construct(PeclMemcached $memcached, $namespace = null) {
        $this->memcached = $memcached;
        $this->namespace = $namespace;
    }

    /**
     * {@inheritdoc}
     */
    public function get($key) {
        return $this->memcached->get($this->getKey($key));
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value, $expire = 0) {
        return $this->memcached->set($this->getKey($key), $value, $expire);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key) {
        return $this->memcached->delete($this->getKey($key));
    }

    /**
     * {@inheritdoc}
     */
    public function increment($key, $amount = 1) {
        return $this->memcached->increment($this->getKey($key), $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function decrement($key, $amount = 1) {
        return $this->memcached->decrement($this->getKey($key), $amount);
    }

    /**
     * Generate a namespaced key
     *
     * @param string $key The key specified by the user
     * @return string A namespaced key
     */
    protected function getKey($key) {
        return $this->namespace . ':' . $key;
    }
}
