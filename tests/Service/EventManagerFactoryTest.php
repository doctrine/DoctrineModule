<?php

declare(strict_types=1);

namespace DoctrineModuleTest\Service;

use Doctrine\Common\EventManager;
use DoctrineModule\Service\EventManagerFactory;
use DoctrineModuleTest\Service\TestAsset\DummyEventSubscriber;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Base test case to be used when a service manager instance is required
 */
class EventManagerFactoryTest extends BaseTestCase
{
    public function testWillInstantiateFromFQCN(): void
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

        /* $var $eventManager EventManager */
        $eventManager = $factory->__invoke($serviceManager, EventManager::class);
        $this->assertInstanceOf(EventManager::class, $eventManager);

        $listeners = $eventManager->getListeners('dummy');
        $this->assertCount(1, $listeners);
    }

    public function testWillAttachEventListenersFromConfiguredInstances(): void
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
                            'subscribers' => [$subscriber],
                        ],
                    ],
                ],
            ]
        );

        /* $var $eventManager EventManager */
        $eventManager = $factory->__invoke($serviceManager, EventManager::class);
        $this->assertInstanceOf(EventManager::class, $eventManager);

        $listeners = $eventManager->getListeners('dummy');
        $this->assertContains($subscriber, $listeners);
    }

    public function testWillAttachEventListenersFromServiceManagerAlias(): void
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
                            'subscribers' => ['dummy-subscriber'],
                        ],
                    ],
                ],
            ]
        );

        /* $var $eventManager EventManager */
        $eventManager = $factory->__invoke($serviceManager, EventManager::class);
        $this->assertInstanceOf(EventManager::class, $eventManager);

        $listeners = $eventManager->getListeners('dummy');
        $this->assertContains($subscriber, $listeners);
    }

    public function testWillRefuseNonExistingSubscriber(): void
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
                            'subscribers' => ['non-existing-subscriber'],
                        ],
                    ],
                ],
            ]
        );

        $this->expectException('InvalidArgumentException');
        $factory->__invoke($serviceManager, EventManager::class);
    }
}
