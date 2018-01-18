<?php

namespace DoctrineModuleTest\Service;

use PHPUnit\Framework\TestCase as BaseTestCase;
use DoctrineModule\Service\EventManagerFactory;
use Zend\ServiceManager\ServiceManager;
use DoctrineModuleTest\Service\TestAsset\DummyEventSubscriber;

/**
 * Base test case to be used when a service manager instance is required
 */
class EventManagerFactoryTest extends BaseTestCase
{
    public function testWillInstantiateFromFQCN()
    {
        $name           = 'eventManagerFactory';
        $factory        = new EventManagerFactory($name);
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'config',
            [
                'doctrine' => [
                    'eventmanager' => [
                        $name => [
                            'subscribers' => [
                                __NAMESPACE__ . '\TestAsset\DummyEventSubscriber',
                            ],
                        ],
                    ],
                ],
            ]
        );

        /* $var $eventManager \Doctrine\Common\EventManager */
        $eventManager = $factory->createService($serviceManager);
        $this->assertInstanceOf('Doctrine\Common\EventManager', $eventManager);

        $listeners = $eventManager->getListeners('dummy');
        $this->assertCount(1, $listeners);
    }

    public function testWillAttachEventListenersFromConfiguredInstances()
    {
        $name           = 'eventManagerFactory';
        $factory        = new EventManagerFactory($name);
        $subscriber     = new DummyEventSubscriber();
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'config',
            [
                'doctrine' => [
                    'eventmanager' => [
                        $name => [
                            'subscribers' => [
                                $subscriber,
                            ],
                        ],
                    ],
                ],
            ]
        );

        /* $var $eventManager \Doctrine\Common\EventManager */
        $eventManager = $factory->createService($serviceManager);
        $this->assertInstanceOf('Doctrine\Common\EventManager', $eventManager);

        $listeners = $eventManager->getListeners();
        $this->assertArrayHasKey('dummy', $listeners);
        $listeners = $eventManager->getListeners('dummy');
        $this->assertContains($subscriber, $listeners);
    }

    public function testWillAttachEventListenersFromServiceManagerAlias()
    {
        $name           = 'eventManagerFactory';
        $factory        = new EventManagerFactory($name);
        $subscriber     = new DummyEventSubscriber();
        $serviceManager = new ServiceManager();
        $serviceManager->setService('dummy-subscriber', $subscriber);
        $serviceManager->setService(
            'config',
            [
                'doctrine' => [
                    'eventmanager' => [
                        $name => [
                            'subscribers' => [
                                'dummy-subscriber',
                            ],
                        ],
                    ],
                ],
            ]
        );

        /* $var $eventManager \Doctrine\Common\EventManager */
        $eventManager = $factory->createService($serviceManager);
        $this->assertInstanceOf('Doctrine\Common\EventManager', $eventManager);

        $listeners = $eventManager->getListeners();
        $this->assertArrayHasKey('dummy', $listeners);
        $listeners = $eventManager->getListeners('dummy');
        $this->assertContains($subscriber, $listeners);
    }

    public function testWillRefuseNonExistingSubscriber()
    {
        $name           = 'eventManagerFactory';
        $factory        = new EventManagerFactory($name);
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'config',
            [
                'doctrine' => [
                    'eventmanager' => [
                        $name => [
                            'subscribers' => [
                                'non-existing-subscriber',
                            ],
                        ],
                    ],
                ],
            ]
        );

        $this->setExpectedException('InvalidArgumentException');
        $factory->createService($serviceManager);
    }
}
