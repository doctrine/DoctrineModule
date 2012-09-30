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

namespace DoctrineModuleTest\Authentication\Storage;

use PHPUnit_Framework_TestCase as BaseTestCase;
use DoctrineModule\Authentication\Storage\ObjectRepository as ObjectRepositoryStorage;
use DoctrineModuleTest\Authentication\Adapter\TestAsset\IdentityObject;
use Zend\Authentication\Storage\NonPersistent as NonPersistentStorage;

/**
 * Tests for the ObjectRepository based authentication adapter
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class ObjectRepositoryTest extends BaseTestCase
{
    public function testCanRetrieveEntityFromObjectRepositoryStorage()
    {
        // Identifier is considered to be username here
        $entity = new IdentityObject();
        $entity->setUsername('a username');
        $entity->setPassword('a password');

        $objectRepository =  $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $objectRepository->expects($this->exactly(1))
                         ->method('find')
                         ->with($this->equalTo('a username'))
                         ->will($this->returnValue($entity));

        $metadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $metadata->expects($this->exactly(1))
                 ->method('getIdentifierValues')
                 ->with($this->equalTo($entity))
                 ->will($this->returnValue($entity->getUsername()));
        
        $storage = new ObjectRepositoryStorage(array(
            'objectRepository' => $objectRepository, 
            'classMetadata' => $metadata, 
            'storage' => new NonPersistentStorage()
        ));

        $storage->write($entity);
        $this->assertFalse($storage->isEmpty());

        $result = $storage->read();
        $this->assertEquals($entity, $result);
        
        $key = $storage->readKeyOnly();
        $this->assertEquals('a username', $key);
    }
}
