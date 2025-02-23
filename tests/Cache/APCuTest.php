<?php declare(strict_types=1);
namespace Imbo\Plugin\MetadataCache\Cache;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(APCu::class)]
class APCuTest extends CacheTests
{
    protected function getAdapter(): APCu
    {
        if (!extension_loaded('apcu')) {
            $this->markTestSkipped('APC(u) is not installed');
        } elseif (!ini_get('apc.enable_cli')) {
            $this->markTestSkipped('apc.enable_cli must be set to On to run this test case');
        }

        return new APCu(uniqid('imbo-metadata-cache-test-', true));
    }
}
