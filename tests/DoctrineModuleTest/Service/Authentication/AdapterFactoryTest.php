<?php

declare(strict_types=1);

namespace DoctrineModuleTest\Service\Authentication;

use DoctrineModule\Service\Authentication\AdapterFactory;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase as BaseTestCase;

class AdapterFactoryTest extends BaseTestCase
{
    public function testWillInstantiateFromFQCN() : void
    {
        $name           = 'testFactory';
        $factory        = new AdapterFactory($name);
        $objectManager  = $this->createMock('Doctrine\Common\Persistence\ObjectManager');
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

        $adapter = $factory->createService($serviceManager);
        $this->assertInstanceOf('DoctrineModule\Authentication\Adapter\ObjectRepository', $adapter);
    }
}
