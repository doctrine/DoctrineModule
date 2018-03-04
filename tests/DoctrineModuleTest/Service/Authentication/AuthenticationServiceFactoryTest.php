<?php

namespace DoctrineModuleTest\Service\Authentication;

use DoctrineModule\Service\Authentication\AuthenticationServiceFactory;
use DoctrineModule\Service\Authentication\AdapterFactory;
use DoctrineModule\Service\Authentication\StorageFactory;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Zend\ServiceManager\ServiceManager;

class AuthenticationServiceFactoryTest extends BaseTestCase
{
    public function testWillInstantiateFromFQCN()
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
            'Zend\Authentication\Storage\Session'
        );
        $serviceManager->setFactory('doctrine.authenticationadapter.' . $name, new AdapterFactory($name));
        $serviceManager->setFactory('doctrine.authenticationstorage.' . $name, new StorageFactory($name));

        $authenticationService = $factory->createService($serviceManager);
        $this->assertInstanceOf('Zend\Authentication\AuthenticationService', $authenticationService);
    }
}
