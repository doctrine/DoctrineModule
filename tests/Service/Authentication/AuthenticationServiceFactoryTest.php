<?php

declare(strict_types=1);

namespace DoctrineModuleTest\Service\Authentication;

use Doctrine\Persistence\ObjectManager;
use DoctrineModule\Service\Authentication\AdapterFactory;
use DoctrineModule\Service\Authentication\AuthenticationServiceFactory;
use DoctrineModule\Service\Authentication\StorageFactory;
use DoctrineModuleTest\Authentication\Adapter\TestAsset\IdentityObject;
use Laminas\Authentication\AuthenticationService;
use Laminas\Authentication\Storage\Session;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase as BaseTestCase;

class AuthenticationServiceFactoryTest extends BaseTestCase
{
    public function testWillInstantiateFromFQCN(): void
    {
        $name    = 'testFactory';
        $factory = new AuthenticationServiceFactory($name);

        $objectManager = $this->createMock(ObjectManager::class);

        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'config',
            [
                'doctrine' => [
                    'authentication' => [
                        $name => [
                            'objectManager' => $objectManager,
                            'identityClass' => IdentityObject::class,
                            'identityProperty' => 'username',
                            'credentialProperty' => 'password',
                        ],
                    ],
                ],
            ],
        );
        $serviceManager->setInvokableClass(
            'DoctrineModule\Authentication\Storage\Session',
            Session::class,
        );
        $serviceManager->setFactory('doctrine.authenticationadapter.' . $name, new AdapterFactory($name));
        $serviceManager->setFactory('doctrine.authenticationstorage.' . $name, new StorageFactory($name));

        $authenticationService = $factory->__invoke($serviceManager, AuthenticationService::class);
        $this->assertInstanceOf(AuthenticationService::class, $authenticationService);
    }
}
