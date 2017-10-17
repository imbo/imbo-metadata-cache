<?php
namespace Imbo\Plugin\MetadataCache\Cache;

abstract class CacheTests extends \PHPUnit_Framework_TestCase {
    private $driver;

    abstract protected function getDriver();

    public function setUp() {
        $this->driver = $this->getDriver();
    }

    public function tearDown() {
        $this->driver = null;
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
        $this->assertFalse($this->driver->get($key));
        $this->driver->set($key, $value);
        $this->assertEquals($value, $this->driver->get($key));
        $this->assertTrue($this->driver->delete($key));
        $this->assertFalse($this->driver->get($key));
    }
}
