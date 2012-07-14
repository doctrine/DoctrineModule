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

namespace DoctrineModule\Cache;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\CacheProvider;
use Zend\Cache\Storage\StorageInterface;
use Zend\Cache\Storage\FlushableInterface;
use Zend\Cache\Storage\TotalSpaceCapableInterface;
use Zend\Cache\Storage\AvailableSpaceCapableInterface;

/**
 * Bridge class that allows usage of a Zend Cache Storage as a Doctrine Cache
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class ZendStorageCache extends CacheProvider
{

    /**
     * @var StorageInterface
     */
    protected $storage;

    /**
     * @param StorageInterface $storage
     */
    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * {@inheritDoc}
     */
    protected function doFetch($id)
    {
        $hit = $this->storage->getItem($id);

        return null === $hit ? false : $hit;
    }

    /**
     * {@inheritDoc}
     */
    protected function doContains($id)
    {
        return $this->storage->hasItem($id);
    }

    /**
     * {@inheritDoc}
     */
    protected function doSave($id, $data, $lifeTime = false)
    {
        // @todo check if lifetime can be set
        return $this->storage->setItem($id, $data);
    }

    /**
     * {@inheritDoc}
     */
    protected function doDelete($id)
    {
        return $this->storage->removeItem($id);
    }

    /**
     * {@inheritDoc}
     */
    protected function doFlush()
    {
        if ($this->storage instanceof FlushableInterface) {
            /* @var $storage FlushableInterface */
            $storage = $this->storage;

            return $storage->flush();
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    protected function doGetStats()
    {
        /* @var $storage TotalSpaceCapableInterface */
        /* @var $storage AvailableSpaceCapableInterface */
        $storage = $this->storage;

        return array(
            Cache::STATS_HITS              => $this->storage->getMetadata(Cache::STATS_HITS),
            Cache::STATS_MISSES            => $this->storage->getMetadata(Cache::STATS_MISSES),
            Cache::STATS_UPTIME            => $this->storage->getMetadata(Cache::STATS_UPTIME),
            Cache::STATS_MEMORY_USAGE      => $storage instanceof TotalSpaceCapableInterface
                ? $storage->getTotalSpace()
                : null,
            Cache::STATS_MEMORY_AVAILIABLE => $storage instanceof AvailableSpaceCapableInterface
                ? $storage->getAvailableSpace()
                : null,
        );
    }
}
