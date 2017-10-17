<?php
namespace Imbo\Plugin\MetadataCache\Cache;

use PHPUnit_Framework_TestCase;

abstract class CacheTests extends PHPUnit_Framework_TestCase {
    /**
     * @var CacheInterface
     */
    private $adpter;

    abstract protected function getAdapter();

    public function setUp() {
        $this->adapter = $this->getAdapter();
    }

    public function tearDown() {
        $this->adapter = null;
    }

    public function getCacheData() {
        return [
            ['key1', 'value'],
            ['key2', 123],
            ['key3', [1, 2, 3]],
            ['key4', new \stdClass()],
        ];
    }

    /**
     * @dataProvider getCacheData
     */
    public function testSetGetAndDelete($key, $value) {
        $this->assertFalse($this->adapter->get($key));
        $this->adapter->set($key, $value);
        $this->assertEquals($value, $this->adapter->get($key));
        $this->assertTrue($this->adapter->delete($key));
        $this->assertFalse($this->adapter->get($key));
    }
}
