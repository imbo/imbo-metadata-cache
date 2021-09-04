<?php declare(strict_types=1);
namespace Imbo\Plugin\MetadataCache\Cache;

/**
 * Cache adapter interface
 */
interface CacheInterface
{
    /**
     * Get a cached value by a key
     *
     * @param string $key The key to get
     * @return mixed Returns the cached value or null if key does not exist
     */
    public function get(string $key);

    /**
     * Store a value in the cache
     *
     * @param string $key The key to associate with the item
     * @param mixed $value The value to store
     * @param int $expire Number of seconds to keep the item in the cache
     * @return bool True on success, false otherwise
     */
    public function set(string $key, $value, int $expire = 0): bool;

    /**
     * Delete an item from the cache
     *
     * @param string $key The key to remove
     * @return bool True on success, false otherwise
     */
    public function delete(string $key): bool;
}
