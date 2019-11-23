<?php

namespace DoctrineModuleTest;

use DoctrineModule\ConfigProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests used to ensure ConfigProvider operates as expected
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  James Titcumb <james@asgrim.com>
 */
class ConfigProviderTest extends TestCase
{
    public function testInvokeHasCorrectKeys()
    {
        $config = (new ConfigProvider())->__invoke();

        self::assertIsArray($config);

        self::assertArrayHasKey('doctrine', $config, 'Expected config to have "doctrine" array key');
        self::assertArrayHasKey('doctrine_factories', $config, 'Expected config to have "doctrine_factories" array key');
        self::assertArrayHasKey('dependencies', $config, 'Expected config to have "dependencies" array key');
        self::assertArrayHasKey('controllers', $config, 'Expected config to have "controllers" array key');
        self::assertArrayHasKey('route_manager', $config, 'Expected config to have "route_manager" array key');
        self::assertArrayHasKey('console', $config, 'Expected config to have "console" array key');

        // Config Provider should not have service_manager key; should only exist in ZF Module
        self::assertArrayNotHasKey('service_manager', $config, 'Config should not have "service_manager" array key');

        self::assertSame($config, unserialize(serialize($config)));
    }
}
