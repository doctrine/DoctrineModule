<?php

namespace DoctrineModuleTest\Authentication\Adapter;

use PHPUnit\Framework\TestCase as BaseTestCase;
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
        $this->expectException(
            'Laminas\Authentication\Adapter\Exception\InvalidArgumentException'
        );
        $this->expectExceptionMessage(
            'Provided $identityProperty is invalid, string given'
        );

        new ObjectRepositoryAdapter(['identity_property' => false]);
    }

    public function testWillRejectInvalidCredentialProperty()
    {
        $this->expectException(
            'Laminas\Authentication\Adapter\Exception\InvalidArgumentException'
        );
        $this->expectExceptionMessage(
            'Provided $credentialProperty is invalid, string given'
        );
        new ObjectRepositoryAdapter(['credential_property' => false]);
    }

    public function testWillRequireIdentityValue()
    {
        $this->expectException(
            'Laminas\Authentication\Adapter\Exception\RuntimeException'
        );
        $this->expectExceptionMessage(
            'A value for the identity was not provided prior to authentication with ObjectRepository authentication '
            . 'adapter'
        );
        $adapter = new ObjectRepositoryAdapter();
        $adapter->setOptions([
            'object_manager' => $this->createMock('Doctrine\Common\Persistence\ObjectManager'),
            'identity_class' => 'DoctrineModuleTest\Authentication\Adapter\TestAsset\IdentityObject',
        ]);
        $adapter->setCredential('a credential');
        $adapter->authenticate();
    }

    public function testWillRequireCredentialValue()
    {
        $this->expectException(
            'Laminas\Authentication\Adapter\Exception\RuntimeException'
        );
        $this->expectExceptionMessage(
            'A credential value was not provided prior to authentication with ObjectRepository authentication adapter'
        );
        $adapter = new ObjectRepositoryAdapter();
        $adapter->setOptions([
            'object_manager' => $this->createMock('Doctrine\Common\Persistence\ObjectManager'),
            'identity_class' => 'DoctrineModuleTest\Authentication\Adapter\TestAsset\IdentityObject',
        ]);

        $adapter->setIdentity('an identity');
        $adapter->authenticate();
    }

    public function testWillRejectInvalidCredentialCallable()
    {
        $this->expectException(
            'Laminas\Authentication\Adapter\Exception\InvalidArgumentException'
        );
        $this->expectExceptionMessage(
            '"array" is not a callable'
        );
        $adapter = new ObjectRepositoryAdapter();
        $adapter->setOptions([
            'object_manager'      => $this->createMock('Doctrine\Common\Persistence\ObjectManager'),
            'identity_class'      => 'DoctrineModuleTest\Authentication\Adapter\TestAsset\IdentityObject',
            'credential_callable' => [],
        ]);

        $adapter->authenticate();
    }

    public function testAuthentication()
    {
        $entity = new IdentityObject();
        $entity->setUsername('a username');
        $entity->setPassword('a password');

        $objectRepository = $this->createMock('Doctrine\Common\Persistence\ObjectRepository');
        $method           = $objectRepository
            ->expects($this->exactly(2))
            ->method('findOneBy')
            ->with($this->equalTo(['username' => 'a username']))
            ->will($this->returnValue($entity));

        $objectManager = $this->createMock('Doctrine\Common\Persistence\ObjectManager');
        $objectManager->expects($this->exactly(2))
                      ->method('getRepository')
                      ->with($this->equalTo('DoctrineModuleTest\Authentication\Adapter\TestAsset\IdentityObject'))
                      ->will($this->returnValue($objectRepository));

        $adapter = new ObjectRepositoryAdapter();
        $adapter->setOptions([
            'object_manager'      => $objectManager,
            'identity_class'      => 'DoctrineModuleTest\Authentication\Adapter\TestAsset\IdentityObject',
            'credential_property' => 'password',
            'identity_property'   => 'username',
        ]);

        $adapter->setIdentity('a username');
        $adapter->setCredential('a password');

        $result = $adapter->authenticate();

        $this->assertTrue($result->isValid());
        $this->assertInstanceOf(
            'DoctrineModuleTest\Authentication\Adapter\TestAsset\IdentityObject',
            $result->getIdentity()
        );

        $method->will($this->returnValue(null));

        $result = $adapter->authenticate();

        $this->assertFalse($result->isValid());
    }

    public function testAuthenticationWithPublicProperties()
    {
        $entity           = new PublicPropertiesIdentityObject();
        $entity->username = 'a username';
        $entity->password = 'a password';

        $objectRepository = $this->createMock('Doctrine\Common\Persistence\ObjectRepository');
        $method           = $objectRepository
            ->expects($this->exactly(2))
            ->method('findOneBy')
            ->with($this->equalTo(['username' => 'a username']))
            ->will($this->returnValue($entity));

        $adapter = new ObjectRepositoryAdapter();
        $adapter->setOptions([
            'object_repository' => $objectRepository,
            'credential_property' => 'password',
            'identity_property' => 'username',
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
        $this->expectException('Laminas\Authentication\Adapter\Exception\UnexpectedValueException');

        $objectRepository = $this->createMock('Doctrine\Common\Persistence\ObjectRepository');
        $objectRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(['username' => 'a username']))
            ->will($this->returnValue(new \stdClass()));

        $adapter = new ObjectRepositoryAdapter();
        $adapter->setOptions([
            'object_repository' => $objectRepository,
            'credential_property' => 'password',
            'identity_property' => 'username',
        ]);

        $adapter->setIdentity('a username');
        $adapter->setCredential('a password');
        $adapter->authenticate();
    }

    public function testCanValidateWithSpecialCrypt()
    {
        $hash   = '$2y$07$usesomesillystringforsalt$';
        $entity = new IdentityObject();
        $entity->setUsername('username');
        // Crypt password using Blowfish
        $entity->setPassword(crypt('password', $hash));

        $objectRepository = $this->createMock('Doctrine\Common\Persistence\ObjectRepository');
        $objectRepository
            ->expects($this->exactly(2))
            ->method('findOneBy')
            ->with($this->equalTo(['username' => 'username']))
            ->will($this->returnValue($entity));

        $adapter = new ObjectRepositoryAdapter();
        $adapter->setOptions([
            'object_repository' => $objectRepository,
            'credential_property' => 'password',
            'identity_property' => 'username',
            // enforced type hinting to verify that closure is invoked correctly
            'credential_callable' => function (IdentityObject $identity, $credentialValue) use ($hash) {
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
        $this->expectException('Laminas\Authentication\Adapter\Exception\UnexpectedValueException');

        $objectRepository = $this->createMock('Doctrine\Common\Persistence\ObjectRepository');
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
        $objectRepository = $this->createMock('Doctrine\Common\Persistence\ObjectRepository');
        $adapter          = new ObjectRepositoryAdapter();
        $entity           = new IdentityObject();

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
