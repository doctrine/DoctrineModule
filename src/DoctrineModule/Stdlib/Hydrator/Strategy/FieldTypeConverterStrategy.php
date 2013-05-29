<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Marco
 */

namespace DoctrineModule\Stdlib\Hydrator\Strategy;

use DateTime;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;

class FieldTypeConverterStrategy implements StrategyInterface
{
    /**
     * @var ClassMetadata
     */
    protected $metadata;

    /**
     * @var string
     */
    protected $fieldName;

    public function __construct(ClassMetadata $metadata, $fieldName)
    {
        $this->metadata  = $metadata;
        $this->fieldName = (string) $fieldName;
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
     * Handles doctrine-specific type conversions for particular field values
     *
     * Currently only handles doctrine ORM "datetime", "time" and "date" mappings
     */
    public function hydrate($value)
    {
        switch ($this->metadata->getTypeOfField($this->fieldName)) {
            case 'datetime':
            case 'time':
            case 'date':
                if ('' === $value) {
                    return null;
                }

                if (is_int($value)) {
                    $dateTime = new DateTime();

                    $dateTime->setTimestamp($value);

                    return $dateTime;
                } elseif (is_string($value)) {
                    return new DateTime($value);
                }
                break;
        }

        return $value;
    }
}
