<?php

declare(strict_types=1);

namespace DoctrineModuleTest\Service\Authentication;

use Doctrine\Persistence\ObjectManager;
use DoctrineModule\Authentication\Storage\ObjectRepository;
use DoctrineModule\Service\Authentication\StorageFactory;
use DoctrineModuleTest\Authentication\Adapter\TestAsset\IdentityObject;
use Laminas\Authentication\Storage\Session;
use Laminas\Authentication\Storage\StorageInterface;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase as BaseTestCase;

class StorageFactoryTest extends BaseTestCase
{
    public function testWillInstantiateFromFQCN(): void
    {
        $name    = 'testFactory';
        $factory = new StorageFactory($name);

        $objectManager = $this->createMock(ObjectManager::class);

        $serviceManager = new ServiceManager();
        $serviceManager->setInvokableClass(
            'DoctrineModule\Authentication\Storage\Session',
            Session::class
        );
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
            ]
        );

        $adapter = $factory->__invoke($serviceManager, ObjectRepository::class);
        $this->assertInstanceOf(ObjectRepository::class, $adapter);
    }

    public function testCanInstantiateStorageFromServiceLocator(): void
    {
        $factory        = new StorageFactory('testFactory');
        $serviceManager = $this->createMock(ServiceManager::class);
        $storage        = $this->createMock(StorageInterface::class);
        $config         = [
            'doctrine' => [
                'authentication' => [
                    'testFactory' => ['storage' => 'some_storage'],
                ],
            ],
        ];

        $serviceManager
            ->expects($this->atLeastOnce())
            ->method('get')
            ->willReturnMap([
                ['config', $config],
                ['some_storage', $storage],
            ]);

        $adapter = $factory->__invoke($serviceManager, ObjectRepository::class);
        $this->assertInstanceOf(ObjectRepository::class, $adapter);
    }
}
