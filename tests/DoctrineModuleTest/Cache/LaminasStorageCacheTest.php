<?php

declare(strict_types=1);

namespace DoctrineModuleTest\Cache;

use ArrayObject;
use Doctrine\Common\Cache\Cache;
use DoctrineModule\Cache\LaminasStorageCache;
use Laminas\Cache\Storage\Adapter\Memory;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the cache bridge
 *
 * @link    http://www.doctrine-project.org/
 */
class LaminasStorageCacheTest extends TestCase
{
    protected function getCacheDriver(): LaminasStorageCache
    {
        return new LaminasStorageCache(new Memory());
    }

    public function testBasics(): void
    {
        $cache = $this->getCacheDriver();

        // Test save
        $cache->save('test_key', 'testing this out');

        // Test contains to test that save() worked
        $this->assertTrue($cache->contains('test_key'));

        // Test fetch
        $this->assertEquals('testing this out', $cache->fetch('test_key'));

        // Test delete
        $cache->save('test_key2', 'test2');
        $cache->delete('test_key2');
        $this->assertFalse($cache->contains('test_key2'));

        // Fetch/save test with objects (Is cache driver serializes/unserializes objects correctly ?)
        $cache->save('test_object_key', new ArrayObject());
        $this->assertInstanceOf('ArrayObject', $cache->fetch('test_object_key'));
    }

    public function testDeleteAll(): void
    {
        $cache = $this->getCacheDriver();
        $cache->save('test_key1', '1');
        $cache->save('test_key2', '2');
        $cache->deleteAll();

        $this->assertFalse($cache->contains('test_key1'));
        $this->assertFalse($cache->contains('test_key2'));
    }

    public function testFlushAll(): void
    {
        $cache = $this->getCacheDriver();
        $cache->save('test_key1', '1');
        $cache->save('test_key2', '2');
        $cache->flushAll();

        $this->assertFalse($cache->contains('test_key1'));
        $this->assertFalse($cache->contains('test_key2'));
    }

    public function testNamespace(): void
    {
        $cache = $this->getCacheDriver();
        $cache->setNamespace('test_');
        $cache->save('key1', 'test');

        $this->assertTrue($cache->contains('key1'));

        $cache->setNamespace('test2_');

        $this->assertFalse($cache->contains('key1'));
    }

    public function testGetStats(): void
    {
        $cache = $this->getCacheDriver();
        $stats = $cache->getStats();

        $this->assertArrayHasKey(Cache::STATS_HITS, $stats);
        $this->assertArrayHasKey(Cache::STATS_MISSES, $stats);
        $this->assertArrayHasKey(Cache::STATS_UPTIME, $stats);
        $this->assertArrayHasKey(Cache::STATS_MEMORY_USAGE, $stats);
        $this->assertArrayHasKey(Cache::STATS_MEMORY_AVAILIABLE, $stats);
    }
}
