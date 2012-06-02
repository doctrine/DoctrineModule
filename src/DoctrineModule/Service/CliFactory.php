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

namespace DoctrineModule\Service;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;


/**
 * Factory responsible for generating the Symfony CLI application required by the Doctrine tools
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Kyle Spraggs <theman@spiffyjr.me>
 */
class CliFactory implements FactoryInterface
{
    /**
     * @var \Zend\EventManager\EventManagerInterface
     */
    protected $events;

    /**
     * @var HelperSet
     */
    protected $helperSet;

    /**
     * @var array
     */
    protected $commands = array();

    /**
     * @param ServiceManager $sm
     * @return \Zend\EventManager\EventManagerInterface
     */
    public function events(ServiceManager $sm)
    {
        if (null === $this->events) {
            $events = $sm->get('EventManager');
            $events->addIdentifiers(array(
                __CLASS__,
                'doctrine'
            ));

            $this->events = $events;
        }

        return $this->events;
    }

    public function createService(ServiceLocatorInterface $sl)
    {
        $cli = new Application();
        $cli->setName('DoctrineModule Command Line Interface');
        $cli->setVersion('dev-master');
        $cli->setHelperSet($this->getHelperSet($sl));

        // Load commands using event
        $this->events($sl)->trigger('loadCliCommands', $cli, array('ServiceManager' => $sl));

        return $cli;
    }

    protected function getHelperSet(ServiceManager $sm)
    {
        if (null === $this->helperSet) {
            $helperSet  = new HelperSet();
            $this->events($sm)->trigger('loadCliHelperSet', $helperSet, array('ServiceManager' => $sm));

            $this->helperSet = $helperSet;
        }

        return $this->helperSet;
    }
}