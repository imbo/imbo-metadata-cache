<?php declare(strict_types=1);
namespace Imbo\Plugin\MetadataCache\Cache;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

abstract class CacheTests extends TestCase
{
    abstract protected function getAdapter(): CacheInterface;

    #[DataProvider('getCacheData')]
    public function testSetGetAndDelete(string $key, mixed $value): void
    {
        $adapter = $this->getAdapter();

        $this->assertFalse(
            $adapter->get($key),
            'Cache retrieval should return false',
        );
        $this->assertTrue(
            $adapter->set($key, $value),
            'Cache storage should return true',
        );
        $this->assertEquals(
            $value,
            $adapter->get($key),
            'Incorrect value returned from cache',
        );
        $this->assertTrue(
            $adapter->delete($key),
            'Cache deletion should return true',
        );
        $this->assertFalse(
            $adapter->get($key),
            'Value has not been removed from cache',
        );
    }

    /**
     * @return array<string,array{key:string,value:mixed}>
     */
    public static function getCacheData(): array
    {
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
}
