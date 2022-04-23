<?php

declare(strict_types=1);

namespace DoctrineModuleTest\Service\Authentication;

use DoctrineModule\Authentication\Adapter\ObjectRepository;
use DoctrineModule\Service\Authentication\AdapterFactory;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase as BaseTestCase;

class AdapterFactoryTest extends BaseTestCase
{
    public function testWillInstantiateFromFQCN(): void
    {
        $name           = 'testFactory';
        $factory        = new AdapterFactory($name);
        $objectManager  = $this->createMock('Doctrine\Persistence\ObjectManager');
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

        $adapter = $factory->__invoke($serviceManager, ObjectRepository::class);
        $this->assertInstanceOf(ObjectRepository::class, $adapter);
    }
}
