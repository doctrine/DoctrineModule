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
use Zend\Cache\Storage\Adapter\AbstractAdapter;

/**
 * Bridge class that allows usage of a Doctrine Cache Storage as a Zend Cache Storage
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class DoctrineCacheStorage extends AbstractAdapter
{
    /**
     * @var Cache
     */
    protected $cache;

    /**
     * {@inheritDoc}
     * @param Cache $cache
     */
    public function __construct($options, Cache $cache)
    {
        parent::__construct($options);

        $this->cache = $cache;
    }

    /**
     * {@inheritDoc}
     */
    protected function internalGetItem(& $normalizedKey, & $success = null, & $casToken = null)
    {
        $key = $this->getOptions()->getNamespace() . $normalizedKey;
        $fetched = $this->cache->fetch($key);
        $success = ($fetched === false ? false : true);

        if ($success) {
            $casToken = $fetched;

            return $fetched;
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    protected function internalSetItem(& $normalizedKey, & $value)
    {
        $key = $this->getOptions()->getNamespace() . $normalizedKey;
        $ttl = $this->getOptions()->getTtl();

        return $this->cache->save($key, $value, $ttl);
    }

    /**
     * {@inheritDoc}
     */
    protected function internalRemoveItem(& $normalizedKey)
    {
        $key = $this->getOptions()->getNamespace() . $normalizedKey;
        if (!$this->cache->contains($key)) {
            return false;
        }

        return $this->cache->delete($key);
    }
}
