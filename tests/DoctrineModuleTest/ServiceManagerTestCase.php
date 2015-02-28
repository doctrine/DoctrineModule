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

namespace DoctrineModuleTest;

use Zend\ServiceManager\ServiceManager;
use Zend\Mvc\Service\ServiceManagerConfig;

/**
 * Base test case to be used when a service manager instance is required
 */
class ServiceManagerTestCase
{
    /**
     * @var array
     */
    protected static $configuration = array();

    /**
     * @static
     * @param array $configuration
     */
    public static function setConfiguration(array $configuration)
    {
        static::$configuration = $configuration;
    }

    /**
     * @static
     * @return array
     */
    public static function getConfiguration()
    {
        return static::$configuration;
    }

    /**
     * Retrieves a new ServiceManager instance
     *
     * @param  array|null     $configuration
     * @return ServiceManager
     */
    public function getServiceManager(array $configuration = null)
    {
        $configuration  = $configuration ?: static::getConfiguration();
        $serviceManager = new ServiceManager(
            new ServiceManagerConfig(
                isset($configuration['service_manager']) ? $configuration['service_manager'] : array()
            )
        );

        $serviceManager->setService('ApplicationConfig', $configuration);
        $serviceManager->setFactory('ServiceListener', 'Zend\Mvc\Service\ServiceListenerFactory');

        /* @var $moduleManager \Zend\ModuleManager\ModuleManagerInterface */
        $moduleManager = $serviceManager->get('ModuleManager');
        $moduleManager->loadModules();

        return $serviceManager;
    }
}
