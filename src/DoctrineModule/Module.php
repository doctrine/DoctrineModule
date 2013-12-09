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

namespace DoctrineModule;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Zend\ModuleManager\Feature\InitProviderInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\ModuleManagerInterface;
use Zend\EventManager\EventInterface;
use Zend\Loader\StandardAutoloader;
use Zend\Console\Adapter\AdapterInterface as Console;

use Symfony\Component\Console\Input\StringInput;
use DoctrineModule\Component\Console\Output\PropertyOutput;

/**
 * Base module for integration of Doctrine projects with ZF2 applications
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @since   0.1.0
 * @author  Kyle Spraggs <theman@spiffyjr.me>
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class Module implements ConfigProviderInterface, InitProviderInterface, BootstrapListenerInterface
{
    /**
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    private $serviceManager;

    /**
     * {@inheritDoc}
     */
    public function init(ModuleManagerInterface $moduleManager)
    {
        AnnotationRegistry::registerLoader(
            function ($className) {
                return class_exists($className);
            }
        );
    }

    /**
     * {@inheritDoc}
     */
    public function onBootstrap(EventInterface $event)
    {
        $this->serviceManager = $event->getTarget()->getServiceManager();
    }

    /**
     * {@inheritDoc}
     */
    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }

    /**
     * {@inheritDoc}
     */
    public function getConsoleUsage(Console $console)
    {
        /* @var $cli \Symfony\Component\Console\Application */
        $cli    = $this->serviceManager->get('doctrine.cli');
        $output = new PropertyOutput();

        $cli->run(new StringInput('list'), $output);

        return $output->getMessage();
    }
}
