<?php

declare(strict_types=1);

namespace DoctrineModuleTest\Service\Authentication;

use DoctrineModule\Service\Authentication\StorageFactory;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase as BaseTestCase;

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
        $this->assertInstanceOf('DoctrineModule\Authentication\Storage\ObjectRepository', $adapter);
    }

    public function testCanInstantiateStorageFromServiceLocator(): void
    {
        $factory        = new StorageFactory('testFactory');
        $serviceManager = $this->createMock('Laminas\ServiceManager\ServiceManager');
        $storage        = $this->createMock('Laminas\Authentication\Storage\StorageInterface');
        $config         = [
            'doctrine' => [
                'authentication' => [
                    'testFactory' => ['storage' => 'some_storage'],
                ],
            ],
        ];

        $serviceManager
            ->expects($this->at(0))
            ->method('get')
            ->with('config')
            ->will($this->returnValue($config));
        $serviceManager
            ->expects($this->at(1))
            ->method('get')
            ->with('some_storage')
            ->will($this->returnValue($storage));

        $this->assertInstanceOf(
            'DoctrineModule\Authentication\Storage\ObjectRepository',
            $factory->createService($serviceManager)
        );
    }
}
