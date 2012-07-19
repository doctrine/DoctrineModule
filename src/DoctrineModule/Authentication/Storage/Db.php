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

namespace DoctrineModule\Authentication\Storage;

use Doctrine\Common\Persistence\ObjectRepository;
use Zend\Authentication\Storage\StorageInterface;

/**
 * This class implements StorageInterface and allow to save the result of an authentication against a database
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @since   0.5.0
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
class Db implements StorageInterface
{
    /**
     * @var ObjectRepository
     */
    protected $objectRepository;

    /**
     * @var StorageInterface
     */
    protected $storage;


    /**
     * @param ObjectRepository $objectRepository
     * @param StorageInterface $storage
     */
    public function __construct(ObjectRepository $objectRepository, StorageInterface $storage)
    {
        $this->objectRepository = $objectRepository;
        $this->storage = $storage;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return $this->storage->isEmpty();
    }

    /**
     * This function assumes that the storage only contains identifier values (which is the case if
     * the ObjectRepository authentication adapter is used).
     *
     * @return null|object
     */
    public function read()
    {
        if (($identity = $this->storage->read())) {
            return $this->objectRepository->find($identity);
        }

        return null;
    }

    /**
     * @param  array|int $contents Identifier value(s) of the entity
     * @return void
     */
    public function write($contents)
    {
        $this->storage->write($contents);
    }

    /**
     * @return void
     */
    public function clear()
    {
        $this->storage->clear();
    }
}
