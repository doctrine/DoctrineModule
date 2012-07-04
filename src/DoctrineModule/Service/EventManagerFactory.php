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

use RuntimeException;
use Doctrine\Common\EventManager;
use DoctrineModule\Service\AbstractFactory;
use Zend\ServiceManager\ServiceLocatorInterface;

class EventManagerFactory extends AbstractFactory
{
    public function createService(ServiceLocatorInterface $sl)
    {
        /** @var $options \DoctrineModule\Options\EventManager */
        $options = $this->getOptions($sl, 'eventmanager');
        $evm     = new EventManager;

        foreach($options->getSubscribers() as $subscriber) {
            if (is_subclass_of($subscriber, 'Doctrine\Common\EventSubscriber')) {
                $evm->addEventSubscriber(new $subscriber);
            } else {
                $evm->addEventSubscriber($sl->get($subscriber));
            }
        }

        return $evm;
    }

    /**
     * Get the class name of the options associated with this factory.
     *
     * @return string
     */
    public function getOptionsClass()
    {
        return 'DoctrineModule\Options\EventManager';
    }
}