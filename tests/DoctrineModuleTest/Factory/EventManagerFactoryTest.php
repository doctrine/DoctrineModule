<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace DoctrineModuleTest\Factory;

use PHPUnit_Framework_TestCase as BaseTestCase;
use DoctrineModule\Factory\EventManagerFactory;
use Zend\ServiceManager\ServiceManager;
use DoctrineModuleTest\Factory\TestAsset\DummyEventSubscriber;

/**
 * Base test case to be used when a service manager instance is required
 */
class EventManagerFactoryTest extends BaseTestCase
{
    public function testWillInstantiateFromFQCN()
    {

        $factory = new EventManagerFactory();
        $factory->setServiceLocator(new ServiceManager);

        /* $var $eventManager \Doctrine\Common\EventManager */
        $eventManager = $factory->create(
            array(
                'subscribers' => array(
                    __NAMESPACE__ . '\TestAsset\DummyEventSubscriber'
                ),
            )
        );
        $this->assertInstanceOf('Doctrine\Common\EventManager', $eventManager);

        $listeners = $eventManager->getListeners('dummy');
        $this->assertCount(1, $listeners);
    }

    public function testWillAttachEventListenersFromConfiguredInstances()
    {
        $factory = new EventManagerFactory;
        $subscriber = new DummyEventSubscriber();

        /* $var $eventManager \Doctrine\Common\EventManager */
        $eventManager = $factory->create(
             array(
                'subscribers' => array(
                    $subscriber,
                ),
            )
        );
        $this->assertInstanceOf('Doctrine\Common\EventManager', $eventManager);

        $listeners = $eventManager->getListeners();
        $this->assertArrayHasKey('dummy', $listeners);
        $listeners = $eventManager->getListeners('dummy');
        $this->assertContains($subscriber, $listeners);
    }

    public function testWillAttachEventListenersFromServiceManagerAlias()
    {

        $factory = new EventManagerFactory();
        $subscriber = new DummyEventSubscriber();
        $serviceManager = new ServiceManager();
        $serviceManager->setService('dummy-subscriber', $subscriber);
        $factory->setServiceLocator($serviceManager);

        /* $var $eventManager \Doctrine\Common\EventManager */
        $eventManager = $factory->create(
            array(
                'subscribers' => array(
                    'dummy-subscriber'
                ),
            )
        );
        $this->assertInstanceOf('Doctrine\Common\EventManager', $eventManager);

        $listeners = $eventManager->getListeners();
        $this->assertArrayHasKey('dummy', $listeners);
        $listeners = $eventManager->getListeners('dummy');
        $this->assertContains($subscriber, $listeners);
    }

    public function testWillRefuseNonExistingSubscriber()
    {

        $factory = new EventManagerFactory;
        $factory->setServiceLocator(new ServiceManager);

        $this->setExpectedException('InvalidArgumentException');
        $factory->create(
             array(
                'subscribers' => array(
                    'non-existing-subscriber'
                ),
             )
        );
    }
}
