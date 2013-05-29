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

namespace DoctrineModule\Stdlib\Hydrator;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\Strategy\AbstractCollectionStrategy;
use InvalidArgumentException;
use Zend\Stdlib\ArrayObject;
use Zend\Stdlib\Hydrator\HydratorInterface;
use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;
use Zend\Stdlib\Hydrator\StrategyEnabledInterface;

/**
 * This hydrator has been completely refactored for DoctrineModule 0.7.0. It provides an easy and powerful way
 * of extracting/hydrator objects in Doctrine, by handling most associations types.
 *
 * Starting from DoctrineModule 0.8.0, the hydrator can be used multiple times with different objects
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @since   0.7.0
 * @author  Michael Gallego <mic.gallego@gmail.com>
 *
 * @todo support multiple metadata at once
 */
class DoctrineObject implements HydratorInterface, StrategyEnabledInterface
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var ClassMetadata
     *
     * @deprecated metadata for the object being currently hydrated
     */
    protected $metadata;

    /**
     * @var bool
     */
    protected $byValue = true;

    /**
     * @var StrategiesContainer[]
     */
    protected $strategiesContainers = array();

    /**
     * @var HydratorInterface|null
     */
    protected $wrappedHydrator;

    /**
     * Constructor
     *
     * @param ObjectManager $objectManager The ObjectManager to use
     * @param bool          $byValue       If set to true, hydrator will always use entity's public API
     */
    public function __construct(ObjectManager $objectManager, $byValue = true)
    {
        $this->objectManager = $objectManager;
        $this->byValue       = (bool) $byValue;
    }

    /**
     * Extract values from an object
     *
     * @param  object $object
     * @return array
     */
    public function extract($object)
    {
        $this->prepare($object);

        return $this->wrappedHydrator->extract($object);
    }

    /**
     * Hydrate $object with the provided $data.
     *
     * @param  array  $data
     * @param  object $object
     * @return object
     */
    public function hydrate(array $data, $object)
    {
        $this->prepare($object);

        return $this->wrappedHydrator->hydrate($data, $object);
    }

    /**
     * Prepare the hydrator by adding strategies to every collection valued associations
     *
     * @param  object $object
     * @return void
     */
    protected function prepare($object)
    {
        $this->metadata = $this->objectManager->getClassMetadata(get_class($object));

        if ($this->byValue) {
            $this->wrappedHydrator = new ByValueObjectHydrator($this->objectManager, $this->getStrategyContainer());
        } else {
            $this->wrappedHydrator = new ByReferenceHydrator($this->objectManager, $this->getStrategyContainer());
        }

        $this->prepareStrategies();
    }

    /**
     * Prepare strategies before the hydrator is used
     *
     * @throws \InvalidArgumentException
     * @return void
     */
    protected function prepareStrategies()
    {
        $associations = $this->metadata->getAssociationNames();

        foreach ($associations as $association) {
            if ($this->metadata->isCollectionValuedAssociation($association)) {
                // Add a strategy if the association has none set by user
                if (!$this->hasStrategy($association)) {
                    if ($this->byValue) {
                        $this->addStrategy($association, new Strategy\AllowRemoveByValue());
                    } else {
                        $this->addStrategy($association, new Strategy\AllowRemoveByReference());
                    }
                }

                $strategy = $this->getStrategy($association);

                if (!$strategy instanceof Strategy\AbstractCollectionStrategy) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'Strategies used for collections valued associations must inherit from '
                            . 'Strategy\AbstractCollectionStrategy, %s given',
                            get_class($strategy)
                        )
                    );
                }

                $strategy->setCollectionName($association)
                         ->setClassMetadata($this->metadata);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function addStrategy($name, StrategyInterface $strategy)
    {
        $this->getStrategyContainer()->addStrategy($name, $strategy);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getStrategy($name)
    {
        return $this->getStrategyContainer()->getStrategy($name);
    }

    /**
     * {@inheritDoc}
     */
    public function hasStrategy($name)
    {
        return $this->getStrategyContainer()->hasStrategy($name);
    }

    /**
     * {@inheritDoc}
     */
    public function removeStrategy($name)
    {
        $this->getStrategyContainer()->removeStrategy($name);

        return $this;
    }

    /**
     * @return StrategiesContainer
     */
    private function getStrategyContainer()
    {
        // @todo there should be one container per object type
        // $objectName = $this->metadata->getName();
        $objectName = 'foo';

        if (isset($this->strategiesContainers[$objectName])) {
            return $this->strategiesContainers[$objectName];
        }

        return $this->strategiesContainers[$objectName] = new StrategiesContainer(
            $this->objectManager/*,
            $this->metadata*/
        );
    }
}
