<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Marco
 */

namespace DoctrineModule\Stdlib\Hydrator\Strategy;

use DateTime;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;

class ToOneAssociationStrategy implements StrategyInterface
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
     * @todo handle null or partially null identifiers (with an utility?)
     */
    public function hydrate($value)
    {
        $targetClass    = $this->metadata->getAssociationTargetClass($this->associationName);
        $targetMetadata = $this->objectManager->getClassMetadata($targetClass);

        if (null === $value || $value instanceof $targetClass) {
            return $value;
        }

        if (is_array($value) && array_keys($value) != $targetMetadata->getIdentifier()) {
            // $value is most likely an array of fieldset data
            $identifiers = array_intersect_key($value, array_flip($targetMetadata->getIdentifier()));
            $object      = $this->objectManager->find($targetClass, $identifiers);

            return $object ?: $targetMetadata->getReflectionClass()->newInstance();
        }

        // @todo should use $targetMetadata->getName()
        return $this->objectManager->find($targetClass, $value);
    }
}
