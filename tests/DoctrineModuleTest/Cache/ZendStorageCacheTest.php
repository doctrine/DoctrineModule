<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace DoctrineModuleTest\Cache;

use DoctrineModule\Cache\ZendStorageCache;
use Doctrine\Common\Cache\Cache;
use Zend\Cache\Storage\Adapter\Memory;
use PHPUnit_Framework_TestCase;

/**
 * Tests for the cache bridge
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class ZendStorageCacheTest extends PHPUnit_Framework_TestCase
{
    /**
     * @return ZendStorageCache
     */
    protected function getCacheDriver()
    {
        return new ZendStorageCache(new Memory());
    }

    public function testBasics()
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
        $cache->save('test_object_key', new \ArrayObject());
        $this->assertTrue($cache->fetch('test_object_key') instanceof \ArrayObject);
    }

    public function testDeleteAll()
    {
        $cache = $this->getCacheDriver();
        $cache->save('test_key1', '1');
        $cache->save('test_key2', '2');
        $cache->deleteAll();

        $this->assertFalse($cache->contains('test_key1'));
        $this->assertFalse($cache->contains('test_key2'));
    }

    public function testFlushAll()
    {
        $cache = $this->getCacheDriver();
        $cache->save('test_key1', '1');
        $cache->save('test_key2', '2');
        $cache->flushAll();

        $this->assertFalse($cache->contains('test_key1'));
        $this->assertFalse($cache->contains('test_key2'));
    }

    public function testNamespace()
    {
        $cache = $this->getCacheDriver();
        $cache->setNamespace('test_');
        $cache->save('key1', 'test');

        $this->assertTrue($cache->contains('key1'));

        $cache->setNamespace('test2_');

        $this->assertFalse($cache->contains('key1'));
    }

    public function testGetStats()
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
