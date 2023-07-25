<?php

declare(strict_types=1);

namespace DoctrineModuleTest\Service\Authentication;

use Doctrine\Persistence\ObjectManager;
use DoctrineModule\Authentication\Adapter\ObjectRepository;
use DoctrineModule\Service\Authentication\AdapterFactory;
use DoctrineModuleTest\Authentication\Adapter\TestAsset\IdentityObject;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase as BaseTestCase;

class AdapterFactoryTest extends BaseTestCase
{
    public function testWillInstantiateFromFQCN(): void
    {
        $name           = 'testFactory';
        $factory        = new AdapterFactory($name);
        $objectManager  = $this->createMock(ObjectManager::class);
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

        $adapter = $factory->__invoke($serviceManager, ObjectRepository::class);
        $this->assertInstanceOf(ObjectRepository::class, $adapter);
    }
}
