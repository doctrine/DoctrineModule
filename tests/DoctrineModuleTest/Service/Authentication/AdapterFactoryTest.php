<?php

namespace DoctrineModuleTest\Service\Authentication;

use DoctrineModule\Service\Authentication\AdapterFactory;
use PHPUnit_Framework_TestCase as BaseTestCase;
use Zend\ServiceManager\ServiceManager;

class AdapterFactoryTest extends BaseTestCase
{
    public function testWillInstantiateFromFQCN()
    {

        $name           = 'testFactory';
        $factory        = new AdapterFactory($name);
        $objectManager  = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
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
