<?php
namespace Imbo\Plugin\MetadataCache\Cache;

/**
 * @coversDefaultClass Imbo\Plugin\MetadataCache\Cache\APCu
 */
class APCuTest extends CacheTests {
    /**
     * {@inheritdoc}
     */
    protected function getAdapter() {
        if (!extension_loaded('apc') && !extension_loaded('apcu')) {
            $this->markTestSkipped('APC(u) is not installed');
        }

        if (!ini_get('apc.enable_cli')) {
            $this->markTestSkipped('apc.enable_cli must be set to On to run this test case');
        }

        return new APCu(uniqid('imbo-metadata-cache-test-', true));
    }
}
