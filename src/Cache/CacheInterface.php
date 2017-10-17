<?php
namespace Imbo\Plugin\MetadataCache\Cache;

/**
 * Cache adapter interface
 */
interface CacheInterface {
    /**
     * Get a cached value by a key
     *
     * @param string $key The key to get
     * @return mixed Returns the cached value or null if key does not exist
     */
    function get($key);

    /**
     * Store a value in the cache
     *
     * @param string $key The key to associate with the item
     * @param mixed $value The value to store
     * @param int $expire Number of seconds to keep the item in the cache
     * @return boolean True on success, false otherwise
     */
    function set($key, $value, $expire = 0);

    /**
     * Delete an item from the cache
     *
     * @param string $key The key to remove
     * @return boolean True on success, false otherwise
     */
    function delete($key);
}
