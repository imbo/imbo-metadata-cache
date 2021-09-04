<?php declare(strict_types=1);
namespace Imbo\Plugin\MetadataCache\Cache;

/**
 * APCu cache
 */
class APCu implements CacheInterface
{
    private string $namespace;

    /**
     * Class constructor
     *
     * @param string $namespace A prefix that will be added to all keys
     */
    public function __construct(string $namespace)
    {
        $this->namespace = $namespace;
    }

    public function get($key)
    {
        return apcu_fetch($this->getKey($key));
    }

    public function set(string $key, $value, int $expire = 0): bool
    {
        return apcu_store($this->getKey($key), $value, $expire);
    }

    public function delete(string $key): bool
    {
        return apcu_delete($this->getKey($key));
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
