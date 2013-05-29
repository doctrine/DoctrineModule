<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Marco
 */

namespace DoctrineModule\Stdlib\Hydrator\Strategy;

use DateTime;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use Traversable;
use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;

class ToManyAssociationStrategy implements StrategyInterface
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
     * @var string
     */
    protected $associationName;

    public function __construct(ObjectManager $objectManager, ClassMetadata $metadata, $associationName)
    {
        $this->objectManager   = $objectManager;
        $this->metadata        = $metadata;
        $this->associationName = (string) $associationName;
    }

    /**
     * {@inheritDoc}
     */
    public function extract($value)
    {
        return $value;
    }

    /**
     * {@inheritDoc}
     *
     * @todo handle null identifiers (with an utility?)
     */
    public function hydrate($values)
    {
        $targetMetadata = $this->objectManager->getClassMetadata(
            $this->metadata->getAssociationTargetClass($this->associationName)
        );

        if (! is_array($values) && !$values instanceof Traversable) {
            $values = (array) $values;
        }

        $collection = array();

        // If the collection contains identifiers, fetch the objects from database
        foreach ($values as $value) {
            $collection[] = $this->objectManager->find($targetMetadata->getName(), $value);
        }

        $collection = array_filter(
            $collection,
            function ($item) {
                return null !== $item;
            }
        );

        return $collection;

        // Set the object so that the strategy can extract the Collection from it

        /** @var \DoctrineModule\Stdlib\Hydrator\Strategy\AbstractCollectionStrategy $collectionStrategy */
        // @todo
        //$collectionStrategy = $this->strategiesContainer->getStrategy($collectionName);
        //$collectionStrategy->setObject($object);
    }
}
