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
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace DoctrineModule;

use Zend\EventManager\Event;
use Zend\Module\Consumer\AutoloaderProvider;
use Zend\Module\Manager;
use Zend\Loader\StandardAutoloader;

/**
 * Base module for integration of Doctrine projects with ZF2 applications
 *
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link    www.doctrine-project.org
 * @since   1.0
 * @version $Revision$
 * @author  Kyle Spraggs <theman@spiffyjr.me>
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class Module implements AutoloaderProvider
{
    /**
     * Retrieves configuration that can be consumed by Zend\Loader\AutoloaderFactory
     *
     * @return array
     */
    public function getAutoloaderConfig()
    {
        if (realpath(__DIR__ . '/vendor/doctrine-common/lib')) {
            return array(
                'Zend\Loader\StandardAutoloader' => array(
                    StandardAutoloader::LOAD_NS => array(
                        __NAMESPACE__                   => __DIR__ . '/src/' . __NAMESPACE__,
                        'Doctrine\Common\DataFixtures'  => __DIR__ . '/vendor/doctrine-data-fixtures/lib/Doctrine/Common/DataFixtures',
                        'Doctrine\Common'               => __DIR__ . '/vendor/doctrine-common/lib/Doctrine/Common',
                        'Symfony\Component\Yaml'        => __DIR__ . '/vendor/symfony-yaml',
                        'Symfony\Component\Console'     => __DIR__ . '/vendor/symfony-console',
                    ),
                ),
            );
        }

        return array();
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
}
