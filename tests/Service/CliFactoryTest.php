<?php

declare(strict_types=1);

namespace DoctrineModuleTest\Service;

use DoctrineModule\Service\CliFactory;
use DoctrineModuleTest\Service\TestAsset\DummyCliCommand;
use Laminas\EventManager\EventInterface;
use Laminas\EventManager\EventManager;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Symfony\Component\Console\Application;

/**
 * Base test case for the setup of Doctrine CLI
 */
class CliFactoryTest extends BaseTestCase
{
    public function testSetupOfDoctrineCli(): void
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService('config', []);
        $serviceManager->setService('EventManager', new EventManager());

        $factory = new CliFactory();
        $app     = $factory->__invoke($serviceManager, 'doctrine.cli');

        $this->assertInstanceOf(Application::class, $app);
    }

    public function testRegistrationOfCustomCliCommand(): void
    {
        $serviceManager = new ServiceManager();
        $eventManager   = new EventManager();

        $eventManager->attach(
            'loadCli.post',
            static function (EventInterface $event): void {
                $target = $event->getTarget();
                if (! ($target instanceof Application)) {
                    return;
                }

                $target->add(new DummyCliCommand());
            },
        );

        $serviceManager->setService('config', []);
        $serviceManager->setService('EventManager', $eventManager);

        $factory = new CliFactory();
        $app     = $factory->__invoke($serviceManager, 'doctrine.cli');

        $this->assertInstanceOf(Application::class, $app);
        $this->assertTrue($app->has('app:dummy-command'));
    }
}
