<?php

declare(strict_types=1);

namespace DoctrineModuleTest\Service\Authentication;

use DoctrineModule\Authentication\Storage\ObjectRepository;
use DoctrineModule\Service\Authentication\StorageFactory;
use Laminas\Authentication\Storage\StorageInterface;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase as BaseTestCase;
use ReflectionClass;

class StorageFactoryTest extends BaseTestCase
{
    public function testWillInstantiateFromFQCN(): void
    {
        $name    = 'testFactory';
        $factory = new StorageFactory($name);

        $objectManager = $this->createMock('Doctrine\Persistence\ObjectManager');

        $serviceManager = new ServiceManager();
        $serviceManager->setInvokableClass(
            'DoctrineModule\Authentication\Storage\Session',
            'Laminas\Authentication\Storage\Session'
        );
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

        $adapter = $factory->createService($serviceManager);
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
            ->expects($this->exactly(2))
            ->method('get')
            ->will($this->onConsecutiveCalls($config, $storage));

        $this->assertInstanceOf(
            ObjectRepository::class,
            $factory->createService($serviceManager)
        );
    }

    public function testCanInstantiateCustomStorage(): void
    {
        $factory        = new StorageFactory('customFactory');
        $serviceManager = $this->createMock(ServiceManager::class);
        $config         = [
            'doctrine' => [
                'authentication' => [
                    'customFactory' => [
                        'sessionContainer' => 'customContainer',
                        'sessionMember' => 'customMember',
                        'storage' => 'custom_storage',
                    ],
                ],
            ],
        ];

        $serviceManager
            ->expects($this->once())
            ->method('get')
            ->with('config')
            ->will($this->returnValue($config));

        $objectRepository = $factory->createService($serviceManager);

        $reflection = new ReflectionClass($objectRepository);
        $property   = $reflection->getProperty('options');
        $property->setAccessible(true);
        $options = $property->getValue($objectRepository);

        $this->assertSame('customContainer', $options->getStorage()->getNamespace());
        $this->assertSame('customMember', $options->getStorage()->getMember());

        $this->assertInstanceOf(
            ObjectRepository::class,
            $objectRepository
        );
    }
}
