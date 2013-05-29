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

use DateTime;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\Strategy\AbstractCollectionStrategy;
use DoctrineModule\Stdlib\Hydrator\Strategy\DoctrineFieldStrategy;
use InvalidArgumentException;
use RuntimeException;
use Traversable;
use Zend\Stdlib\ArrayObject;
use Zend\Stdlib\ArrayUtils;
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
     */
    protected $metadata;

    /**
     * @var bool
     */
    protected $byValue = true;

    /**
     * @var StrategyInterface[]
     */
    protected $fieldStrategies = array();

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

        if ($byValue) {
            $this->wrappedHydrator = new ByValueObjectHydrator($objectManager);
        } else {
            $this->wrappedHydrator = new ByReferenceHydrator($objectManager);
        }
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

        foreach (array_merge($this->metadata->getFieldNames(), $this->metadata->getAssociationNames()) as $field) {
            $this->fieldStrategies[] = new DoctrineFieldStrategy($this->metadata, $field);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function addStrategy($name, StrategyInterface $strategy)
    {
        $this->wrappedHydrator->addStrategy($name, $strategy);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getStrategy($name)
    {
        return $this->wrappedHydrator->getStrategy($name);
    }

    /**
     * {@inheritDoc}
     */
    public function hasStrategy($name)
    {
        return $this->wrappedHydrator->hasStrategy($name);
    }

    /**
     * {@inheritDoc}
     */
    public function removeStrategy($name)
    {
        $this->wrappedHydrator->removeStrategy($name);

        return $this;
    }
}
