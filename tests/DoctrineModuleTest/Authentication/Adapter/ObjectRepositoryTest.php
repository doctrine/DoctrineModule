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

namespace DoctrineModuleTest\Authentication\Adapter;

use PHPUnit_Framework_TestCase as BaseTestCase;
use DoctrineModule\Authentication\Adapter\ObjectRepository as ObjectRepositoryAdapter;
use DoctrineModuleTest\Authentication\Adapter\TestAsset\IdentityObject;
use DoctrineModuleTest\Authentication\Adapter\TestAsset\PublicPropertiesIdentityObject;

/**
 * Tests for the ObjectRepository based authentication adapter
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class ObjectRepositoryTest extends BaseTestCase
{
    public function testWillRejectInvalidIdentityProperty()
    {
        $this->setExpectedException(
            'Zend\Authentication\Adapter\Exception\InvalidArgumentException',
            'Provided $identityProperty is invalid, boolean given'
        );
        new ObjectRepositoryAdapter(array(
                'identity_property' => false
            )
        );
    }

    public function testWillRejectInvalidCredentialProperty()
    {
        $this->setExpectedException(
            'Zend\Authentication\Adapter\Exception\InvalidArgumentException',
            'Provided $credentialProperty is invalid, boolean given'
        );
        new ObjectRepositoryAdapter(array(
                'credential_property' => false
            )
        );
    }

    public function testWillRequireIdentityValue()
    {
        $this->setExpectedException(
            'Zend\Authentication\Adapter\Exception\RuntimeException',
            'A value for the identity was not provided prior to authentication with ObjectRepository authentication '
                . 'adapter'
        );
        $adapter = new ObjectRepositoryAdapter();
        $adapter->setOptions(array(
            'object_manager' => $this->getMock('Doctrine\Common\Persistence\ObjectManager'),
            'identity_class' => 'DoctrineModuleTest\Authentication\Adapter\TestAsset\IdentityObject',
        ));
        $adapter->setCredentialValue('a credetential');
        $adapter->authenticate();
    }

    public function testWillRequireCredentialValue()
    {
        $this->setExpectedException(
            'Zend\Authentication\Adapter\Exception\RuntimeException',
            'A credential value was not provided prior to authentication with ObjectRepository authentication adapter'
        );
        $adapter = new ObjectRepositoryAdapter();
        $adapter->setOptions(array(
            'object_manager' => $this->getMock('Doctrine\Common\Persistence\ObjectManager'),
            'identity_class' => 'DoctrineModuleTest\Authentication\Adapter\TestAsset\IdentityObject',
        ));

        $adapter->setIdentityValue('an identity');
        $adapter->authenticate();
    }

    public function testWillRejectInvalidCredentialCallable()
    {
        $this->setExpectedException(
            'Zend\Authentication\Adapter\Exception\InvalidArgumentException',
            '"array" is not a callable'
        );
        $adapter = new ObjectRepositoryAdapter();
        $adapter->setOptions(array(
            'object_manager' => $this->getMock('Doctrine\Common\Persistence\ObjectManager'),
            'identity_class' => 'DoctrineModuleTest\Authentication\Adapter\TestAsset\IdentityObject',
            'credential_callable' => array()
        ));

        $adapter->authenticate();
    }

    public function testAuthentication()
    {
        $entity = new IdentityObject();
        $entity->setUsername('a username');
        $entity->setPassword('a password');

        $objectRepository =  $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $method = $objectRepository
            ->expects($this->exactly(2))
            ->method('findOneBy')
            ->with($this->equalTo(array('username' => 'a username')))
            ->will($this->returnValue($entity));

        $objectManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $objectManager->expects($this->exactly(2))
                      ->method('getRepository')
                      ->with($this->equalTo('DoctrineModuleTest\Authentication\Adapter\TestAsset\IdentityObject'))
                      ->will($this->returnValue($objectRepository));

        $adapter = new ObjectRepositoryAdapter();
        $adapter->setOptions(array(
            'object_manager' => $objectManager,
            'identity_class' => 'DoctrineModuleTest\Authentication\Adapter\TestAsset\IdentityObject',
            'credential_property' => 'password',
            'identity_property' => 'username'
        ));

        $adapter->setIdentityValue('a username');
        $adapter->setCredentialValue('a password');

        $result = $adapter->authenticate();

        $this->assertTrue($result->isValid());
        $this->assertInstanceOf('DoctrineModuleTest\Authentication\Adapter\TestAsset\IdentityObject', $result->getIdentity());

        $method->will($this->returnValue(null));

        $result = $adapter->authenticate();

        $this->assertFalse($result->isValid());
    }

    public function testAuthenticationWithPublicProperties()
    {
        $entity = new PublicPropertiesIdentityObject();
        $entity->username = 'a username';
        $entity->password = 'a password';

        $objectRepository =  $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $method = $objectRepository
            ->expects($this->exactly(2))
            ->method('findOneBy')
            ->with($this->equalTo(array('username' => 'a username')))
            ->will($this->returnValue($entity));

        $adapter = new ObjectRepositoryAdapter();
        $adapter->setOptions(array(
            'object_repository' => $objectRepository,
            'credential_property' => 'password',
            'identity_property' => 'username'
        ));

        $adapter->setIdentityValue('a username');
        $adapter->setCredentialValue('a password');

        $result = $adapter->authenticate();

        $this->assertTrue($result->isValid());

        $method->will($this->returnValue(null));

        $result = $adapter->authenticate();

        $this->assertFalse($result->isValid());
    }

    public function testWillRefuseToAuthenticateWithoutGettersOrPublicMethods()
    {
        $this->setExpectedException('Zend\Authentication\Adapter\Exception\UnexpectedValueException');

        $objectRepository =  $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $objectRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(array('username' => 'a username')))
            ->will($this->returnValue(new \stdClass()));

        $adapter = new ObjectRepositoryAdapter();
        $adapter->setOptions(array(
            'object_repository' => $objectRepository,
            'credential_property' => 'password',
            'identity_property' => 'username'
        ));

        $adapter->setIdentityValue('a username');
        $adapter->setCredentialValue('a password');
        $adapter->authenticate();
    }

    public function testCanValidateWithSpecialCrypt()
    {
        $hash = '$2y$07$usesomesillystringforsalt$';
        $entity = new IdentityObject();
        $entity->setUsername('username');
        // Crypt password using Blowfish
        $entity->setPassword(crypt('password', $hash));

        $objectRepository =  $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $objectRepository
            ->expects($this->exactly(2))
            ->method('findOneBy')
            ->with($this->equalTo(array('username' => 'username')))
            ->will($this->returnValue($entity));

        $adapter = new ObjectRepositoryAdapter();
        $adapter->setOptions(array(
            'object_repository' => $objectRepository,
            'credential_property' => 'password',
            'identity_property' => 'username',
            // enforced type hinting to verify that closure is invoked correctly
            'credential_callable' => function(IdentityObject $identity, $credentialValue) use ($hash) {
                return $identity->getPassword() === crypt($credentialValue, $hash);
            }
        ));

        $adapter->setIdentityValue('username');
        $adapter->setCredentialValue('password');

        $result = $adapter->authenticate();

        $this->assertTrue($result->isValid());

        $adapter->setCredentialValue('wrong password');
        $result = $adapter->authenticate();

        $this->assertFalse($result->isValid());
    }

    public function testWillRefuseToAuthenticateWhenInvalidInstanceIsFound()
    {
        $this->setExpectedException('Zend\Authentication\Adapter\Exception\UnexpectedValueException');

        $objectRepository =  $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $objectRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(array('username' => 'a username')))
            ->will($this->returnValue(new \stdClass()));

        $adapter = new ObjectRepositoryAdapter();
        $adapter->setOptions(array(
            'object_repository' => $objectRepository,
            'credential_property' => 'password',
            'identity_property' => 'username'
        ));

        $adapter->setIdentityValue('a username');
        $adapter->setCredentialValue('a password');

        $adapter->authenticate();
    }
}
