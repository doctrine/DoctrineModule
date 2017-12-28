<?php

namespace DoctrineModuleTest\Service\Authentication;

use DoctrineModule\Service\Authentication\StorageFactory;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Zend\ServiceManager\ServiceManager;

class StorageFactoryTest extends BaseTestCase
{
    public function testWillInstantiateFromFQCN()
    {
        $name    = 'testFactory';
        $factory = new StorageFactory($name);

        $objectManager = $this->createMock('Doctrine\Common\Persistence\ObjectManager');

        $serviceManager = new ServiceManager();
        $serviceManager->setInvokableClass(
            'DoctrineModule\Authentication\Storage\Session',
            'Zend\Authentication\Storage\Session'
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

    public function testCanInstantiateStorageFromServiceLocator()
    {
        $factory        = new StorageFactory('testFactory');
        $serviceManager = $this->createMock('Zend\ServiceManager\ServiceManager');
        $storage        = $this->createMock('Zend\Authentication\Storage\StorageInterface');
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
