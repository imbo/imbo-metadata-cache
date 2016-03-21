<?php
namespace ImboTest\Cache;

use Imbo\Cache\APCu;

/**
 * @covers Imbo\Cache\APCu
 */
class APCuTest extends CacheTests {
    protected function getDriver() {
        if (!extension_loaded('apc') && !extension_loaded('apcu')) {
            $this->markTestSkipped('APC(u) is not installed');
        }

        if (!ini_get('apc.enable_cli')) {
            $this->markTestSkipped('apc.enable_cli must be set to On to run this test case');
        }

        return new APCu('ImboTestSuite');
    }
}
