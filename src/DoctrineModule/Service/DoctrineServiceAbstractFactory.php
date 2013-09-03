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

use DoctrineModule\Builder\BuilderInterface;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Abstract service factory capable of instantiating services whose names match the
 * pattern <code>doctrine.foo.bar.baz</code>
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Marco Pivetta <ocramius@gmail.com>
 * @author  Tim Roediger  <superdweebie@gmail.com>
 */
class DoctrineServiceAbstractFactory implements AbstractFactoryInterface
{

    const DOCTRINE_PREFIX = 'doctrine';
    const BUILDER_PREFIX  = 'builder';

    /**
     * {@inheritDoc}
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return false !== $this->getBuilderMapping($serviceLocator, $name);
    }

    /**
     * {@inheritDoc}
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $mapping = $this->getBuilderMapping($serviceLocator, $name);

        /* @var $builder \DoctrineModule\Builder\BuilderInterface */
        $builder = $serviceLocator->get($mapping['builderName']);

        if (! $builder instanceof BuilderInterface) {
            throw new ServiceNotFoundException(
                sprintf(
                    '%s service did not return an instance of \DoctrineModule\Builder\AbstractBuilderInterface',
                    $mapping['builderName']
                )
            );
        }

        return $builder->build($mapping['options']);
    }

    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @param string                                       $name
     *
     * @return bool|array
     */
    private function getBuilderMapping(ServiceLocatorInterface $serviceLocator, $name)
    {
        $pieces = explode('.', $name);

        if (count($pieces) < 2) {
            return false;
        }
        if (array_shift($pieces) !== self::DOCTRINE_PREFIX) {
            return false;
        }
        if ($pieces[0] === self::BUILDER_PREFIX) {
            return false;
        }

        $builderName = implode(
            '.',
            array_merge(
                array(self::DOCTRINE_PREFIX, self::BUILDER_PREFIX),
                array_slice($pieces, 0, count($pieces) - 1)
            )
        );

        $options = $serviceLocator->get('Config');
        $options = $options['doctrine'];
        foreach ($pieces as $piece) {
            if (isset($options[$piece])) {
                $options = $options[$piece];
            } else {
                return false;
            }
        }

        return array(
            'builderName' => $builderName,
            'options'     => $options
        );
    }
}
