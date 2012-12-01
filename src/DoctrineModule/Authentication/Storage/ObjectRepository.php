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

use DoctrineModule\Options\Authentication as AuthenticationOptions;
use Zend\Authentication\Storage\StorageInterface;

/**
 * This class implements StorageInterface and allow to save the result of an authentication against an object repository
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @since   0.5.0
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
class ObjectRepository implements StorageInterface
{

    /**
     *
     * @var \DoctrineModule\Options\Authentication
     */
    protected $options;

    /**
     * @param  array | \DoctrineModule\Options\Authentication $options
     * @return ObjectRepository
     */
    public function setOptions($options)
    {
        if (!$options instanceof AuthenticationOptions) {
            $options = new AuthenticationOptions($options);
        }

        $this->options = $options;
        return $this;
    }
    
    /**
     * Constructor
     *
     * @param array | \DoctrineModule\Options\Authentication $options
     */
    public function __construct($options = array())
    {
        $this->setOptions($options);
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return $this->options->getStorage()->isEmpty();
    }

    /**
     * This function assumes that the storage only contains identifier values (which is the case if
     * the ObjectRepository authentication adapter is used).
     *
     * @return null|object
     */
    public function read()
    {
        if (($identity = $this->options->getStorage()->read())) {
            return $this->options->getObjectRepository()->find($identity);
        }

        return null;
    }

    /**
     * Will return the key of the identity. If only the key is needed, this avoids an
     * unnecessary db call
     * 
     * @return mixed
     */
    public function readKeyOnly(){
        return $identity = $this->options->getStorage()->read();
    }
    
    /**
     * @param  object $identity
     * @return void
     */
    public function write($identity)
    {
        $metadataInfo     = $this->options->getClassMetadata();
        $identifierValues = $metadataInfo->getIdentifierValues($identity);

        $this->options->getStorage()->write($identifierValues);
    }

    /**
     * @return void
     */
    public function clear()
    {
        $this->options->getStorage()->clear();
    }
}
