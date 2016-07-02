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

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use DoctrineModule\Authentication\Adapter\ObjectRepository as ObjectRepositoryAdapter;
use Zend\Authentication\Adapter\Exception;

/**
 * Tests for the ObjectRepository based authentication adapter
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class ObjectRepositoryTest extends \PHPUnit_Framework_TestCase
{
    public function testWillRejectInvalidIdentityProperty()
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Provided $identityProperty is invalid, boolean given');

        new ObjectRepositoryAdapter(['identity_property' => false]);
    }

    public function testWillRejectInvalidCredentialProperty()
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Provided $credentialProperty is invalid, boolean given');

        new ObjectRepositoryAdapter(['credential_property' => false]);
    }

    public function testWillRequireIdentityValue()
    {
        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage(
            'A value for the identity was not provided prior to authentication with ObjectRepository authentication'
            . ' adapter'
        );

        $adapter = new ObjectRepositoryAdapter();
        $adapter->setOptions([
            'object_manager' => $this->createMock(ObjectManager::class),
            'identity_class' => TestAsset\IdentityObject::class,
        ]);
        $adapter->setCredential('a credential');
        $adapter->authenticate();
    }

    public function testWillRequireCredentialValue()
    {
        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage(
            'A credential value was not provided prior to authentication with ObjectRepository authentication adapter'
        );

        $adapter = new ObjectRepositoryAdapter();
        $adapter->setOptions([
            'object_manager' => $this->createMock(ObjectManager::class),
            'identity_class' => TestAsset\IdentityObject::class,
        ]);

        $adapter->setIdentity('an identity');
        $adapter->authenticate();
    }

    public function testWillRejectInvalidCredentialCallable()
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('"array" is not a callable');

        $adapter = new ObjectRepositoryAdapter();
        $adapter->setOptions([
            'object_manager'      => $this->createMock(ObjectManager::class),
            'identity_class'      => TestAsset\IdentityObject::class,
            'credential_callable' => [],
        ]);

        $adapter->authenticate();
    }

    public function testAuthentication()
    {
        $entity = new TestAsset\IdentityObject();
        $entity->setUsername('a username');
        $entity->setPassword('a password');

        $objectRepository = $this->createMock(ObjectRepository::class);
        $method           = $objectRepository
            ->expects($this->exactly(2))
            ->method('findOneBy')
            ->with($this->equalTo(['username' => 'a username']))
            ->will($this->returnValue($entity));

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects($this->exactly(2))
                      ->method('getRepository')
                      ->with($this->equalTo(TestAsset\IdentityObject::class))
                      ->will($this->returnValue($objectRepository));

        $adapter = new ObjectRepositoryAdapter();
        $adapter->setOptions([
            'object_manager'      => $objectManager,
            'identity_class'      => TestAsset\IdentityObject::class,
            'credential_property' => 'password',
            'identity_property'   => 'username',
        ]);

        $adapter->setIdentity('a username');
        $adapter->setCredential('a password');

        $result = $adapter->authenticate();

        $this->assertTrue($result->isValid());
        $this->assertInstanceOf(TestAsset\IdentityObject::class, $result->getIdentity());

        $method->will($this->returnValue(null));

        $result = $adapter->authenticate();

        $this->assertFalse($result->isValid());
    }

    public function testAuthenticationWithPublicProperties()
    {
        $entity           = new TestAsset\PublicPropertiesIdentityObject();
        $entity->username = 'a username';
        $entity->password = 'a password';

        $objectRepository = $this->createMock(ObjectRepository::class);
        $method           = $objectRepository
            ->expects($this->exactly(2))
            ->method('findOneBy')
            ->with($this->equalTo(['username' => 'a username']))
            ->will($this->returnValue($entity));

        $adapter = new ObjectRepositoryAdapter();
        $adapter->setOptions([
            'object_repository'   => $objectRepository,
            'credential_property' => 'password',
            'identity_property'   => 'username',
        ]);

        $adapter->setIdentity('a username');
        $adapter->setCredential('a password');

        $result = $adapter->authenticate();

        $this->assertTrue($result->isValid());

        $method->will($this->returnValue(null));

        $result = $adapter->authenticate();

        $this->assertFalse($result->isValid());
    }

    public function testWillRefuseToAuthenticateWithoutGettersOrPublicMethods()
    {
        $this->expectException(Exception\UnexpectedValueException::class);

        $objectRepository =  $this->createMock(ObjectRepository::class);
        $objectRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(['username' => 'a username']))
            ->will($this->returnValue(new \stdClass()));

        $adapter = new ObjectRepositoryAdapter();
        $adapter->setOptions([
            'object_repository'   => $objectRepository,
            'credential_property' => 'password',
            'identity_property'   => 'username',
        ]);

        $adapter->setIdentity('a username');
        $adapter->setCredential('a password');
        $adapter->authenticate();
    }

    public function testCanValidateWithSpecialCrypt()
    {
        $hash   = '$2y$07$usesomesillystringforsalt$';
        $entity = new TestAsset\IdentityObject();
        $entity->setUsername('username');
        // Crypt password using Blowfish
        $entity->setPassword(crypt('password', $hash));

        $objectRepository =  $this->createMock(ObjectRepository::class);
        $objectRepository
            ->expects($this->exactly(2))
            ->method('findOneBy')
            ->with($this->equalTo(['username' => 'username']))
            ->will($this->returnValue($entity));

        $adapter = new ObjectRepositoryAdapter();
        $adapter->setOptions([
            'object_repository'   => $objectRepository,
            'credential_property' => 'password',
            'identity_property'   => 'username',
            // enforced type hinting to verify that closure is invoked correctly
            'credential_callable' => function (TestAsset\IdentityObject $identity, $credentialValue) use ($hash) {
                return $identity->getPassword() === crypt($credentialValue, $hash);
            },
        ]);

        $adapter->setIdentity('username');
        $adapter->setCredential('password');

        $result = $adapter->authenticate();

        $this->assertTrue($result->isValid());

        $adapter->setCredential('wrong password');
        $result = $adapter->authenticate();

        $this->assertFalse($result->isValid());
    }

    public function testWillRefuseToAuthenticateWhenInvalidInstanceIsFound()
    {
        $this->expectException(Exception\UnexpectedValueException::class);

        $objectRepository =  $this->createMock(ObjectRepository::class);
        $objectRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(['username' => 'a username']))
            ->will($this->returnValue(new \stdClass()));

        $adapter = new ObjectRepositoryAdapter();
        $adapter->setOptions([
            'object_repository'   => $objectRepository,
            'credential_property' => 'password',
            'identity_property'   => 'username',
        ]);

        $adapter->setIdentity('a username');
        $adapter->setCredential('a password');

        $adapter->authenticate();
    }

    public function testWillNotCastAuthCredentialValue()
    {
        $objectRepository = $this->createMock(ObjectRepository::class);
        $adapter          = new ObjectRepositoryAdapter();
        $entity           = new TestAsset\IdentityObject();

        $entity->setPassword(0);
        $adapter->setOptions([
             'object_repository'   => $objectRepository,
             'credential_property' => 'password',
             'identity_property'   => 'username',
        ]);
        $adapter->setIdentity('a username');
        $adapter->setCredential('00000');
        $objectRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(['username' => 'a username']))
            ->will($this->returnValue($entity));

        $this->assertFalse($adapter->authenticate()->isValid());
    }
}
