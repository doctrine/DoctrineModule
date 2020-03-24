<?php

declare(strict_types=1);

namespace DoctrineModuleTest\Service\Authentication;

use DoctrineModule\Service\Authentication\AdapterFactory;
use DoctrineModule\Service\Authentication\AuthenticationServiceFactory;
use DoctrineModule\Service\Authentication\StorageFactory;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase as BaseTestCase;

class AuthenticationServiceFactoryTest extends BaseTestCase
{
    public function testWillInstantiateFromFQCN() : void
    {
        $name    = 'testFactory';
        $factory = new AuthenticationServiceFactory($name);

        $objectManager = $this->createMock('Doctrine\Common\Persistence\ObjectManager');

        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'config',
            [
                'doctrine' => [
                    'authentication' => [
                        $name => [
                            'objectManager' => $objectManager,
                            'identityClass' => 'DoctrineModuleTest\Authentication\Adapter\TestAsset\IdentityObject',
                            'identityProperty' => 'username',
                            'credentialProperty' => 'password',
                        ],
                    ],
                ],
            ]
        );
        $serviceManager->setInvokableClass(
            'DoctrineModule\Authentication\Storage\Session',
            'Laminas\Authentication\Storage\Session'
        );
        $serviceManager->setFactory('doctrine.authenticationadapter.' . $name, new AdapterFactory($name));
        $serviceManager->setFactory('doctrine.authenticationstorage.' . $name, new StorageFactory($name));

        $authenticationService = $factory->createService($serviceManager);
        $this->assertInstanceOf('Laminas\Authentication\AuthenticationService', $authenticationService);
    }
}
