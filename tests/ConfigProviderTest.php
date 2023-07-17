<?php

declare(strict_types=1);

namespace DoctrineModuleTest;

use DoctrineModule\ConfigProvider;
use Laminas\Cache\Storage\Adapter\Filesystem;
use PHPUnit\Framework\TestCase;

use function serialize;
use function unserialize;

/**
 * Tests used to ensure ConfigProvider operates as expected
 */
class ConfigProviderTest extends TestCase
{
    public function testInvokeHasCorrectKeys(): void
    {
        $config = (new ConfigProvider())->__invoke();

        self::assertIsArray($config);

        self::assertArrayHasKey('caches', $config, 'Expected config to have "caches" array key');
        self::assertArrayHasKey('doctrine', $config, 'Expected config to have "doctrine" array key');
        self::assertArrayHasKey(
            'doctrine_factories',
            $config,
            'Expected config to have "doctrine_factories" array key',
        );
        self::assertArrayHasKey('dependencies', $config, 'Expected config to have "dependencies" array key');

        // Config Provider should not have service_manager key; should only exist in ZF Module
        self::assertArrayNotHasKey('service_manager', $config, 'Config should not have "service_manager" array key');

        self::assertSame($config, unserialize(serialize($config)));
    }

    public function testDoctrineCompatibleCacheKeyConfiguration(): void
    {
        $config  = (new ConfigProvider())->getCachesConfig()['doctrinemodule.cache.filesystem'];
        $adapter = new Filesystem($config['options']);
        $key     = 'MyTestKey[something\inside\here#with$specialChars]';
        $adapter->setItem($key, 'foo');
        $this->assertEquals('foo', $adapter->getItem($key));
    }
}
