<?php
namespace Imbo\Plugin\MetadataCache\Cache;

/**
 * APCu cache
 */
class APCu implements CacheInterface {
    /**
     * Key namespace
     *
     * @var string
     */
    private $namespace;

    /**
     * Class constructor
     *
     * @param string $namespace A prefix that will be added to all keys
     */
    public function __construct($namespace) {
        $this->namespace = $namespace;
    }

    /**
     * {@inheritdoc}
     */
    public function get($key) {
        return apc_fetch($this->getKey($key));
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value, $expire = 0) {
        return apc_store($this->getKey($key), $value, $expire);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key) {
        return apc_delete($this->getKey($key));
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
