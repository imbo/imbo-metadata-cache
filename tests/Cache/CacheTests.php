<?php
namespace Imbo\Plugin\MetadataCache\Cache;

use PHPUnit_Framework_TestCase;
use stdClass;

abstract class CacheTests extends PHPUnit_Framework_TestCase {
    /**
     * @var CacheInterface
     */
    private $adpter;

    /**
     * Get the adapter to test
     *
     * @return CacheInterface
     */
    abstract protected function getAdapter();

    /**
     * Data provider
     *
     * @return array[]
     */
    public function getCacheData() {
        return [
            'string value' => [
                'key' => 'key1',
                'value' => 'value',
            ],
            'numeric value' => [
                'key' => 'key2',
                'value' => 123,
            ],
            'list value' => [
                'key' => 'key3',
                'value' => [1, 2, 3],
            ],
            'object value' => [
                'key' => 'key4',
                'value' => new stdClass(),
            ],
        ];
    }

    /**
     * @dataProvider getCacheData
     * @param string $key The cache key
     * @param mixed $value The cache value
     * @covers ::__construct
     * @covers ::get
     * @covers ::set
     * @covers ::delete
     */
    public function testSetGetAndDelete($key, $value) {
        $adapter = $this->getAdapter();

        $this->assertFalse(
            $adapter->get($key),
            'Cache retrieval should return boolean false'
        );
        $this->assertTrue(
            $adapter->set($key, $value),
            'Cache storage should return boolean true'
        );
        $this->assertEquals(
            $value,
            $adapter->get($key),
            'Incorrect value return from cache'
        );
        $this->assertTrue(
            $adapter->delete($key),
            'Cache deletion should return boolean false'
        );
        $this->assertFalse(
            $adapter->get($key),
            'Value does not seem to have been removed from cache'
        );
    }
}
