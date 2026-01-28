<?php declare(strict_types=1);

namespace Imbo\Plugin\MetadataCache\Cache;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\RequiresSetting;

#[CoversClass(APCu::class)]
#[RequiresPhpExtension('apcu')]
#[RequiresSetting('apc.enable_cli', '1')]
class APCuTest extends CacheTests
{
    protected function getAdapter(): APCu
    {
        return new APCu(uniqid('imbo-metadata-cache-test-', true));
    }
}
