<?php

declare(strict_types=1);

namespace DoctrineModule\Form\Element;

use Laminas\Form\Element\Select as SelectElement;
use Laminas\Stdlib\ArrayUtils;
use Traversable;

use function array_map;
use function is_array;

class ObjectSelectV3Polyfill extends SelectElement
{
    use GetProxy;

    /**
     * @param array|Traversable $options
     *
     * {@inheritDoc}
     */
    public function setOptions($options): self
    {
        $this->getProxy()->setOptions($options);

        return parent::setOptions($options);
    }

    /**
     * @param mixed $value
     *
     * {@inheritDoc}
     */
    public function setOption($key, $value): self
    {
        $this->getProxy()->setOptions([$key => $value]);

        return parent::setOption($key, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function setValue($value)
    {
        $multiple = $this->getAttribute('multiple');

        if ($multiple === true || $multiple === 'multiple') {
            if ($value instanceof Traversable) {
                $value = ArrayUtils::iteratorToArray($value);
            } elseif ($value === null) {
                return parent::setValue([]);
            } elseif (! is_array($value)) {
                $value = (array) $value;
            }

            return parent::setValue(array_map([$this->getProxy(), 'getValue'], $value));
        }

        return parent::setValue($this->getProxy()->getValue($value));
    }

    /**
     * {@inheritDoc}
     */
    public function getValueOptions(): array
    {
        if (! empty($this->valueOptions)) {
            return $this->valueOptions;
        }

        $proxyValueOptions = $this->getProxy()->getValueOptions();

        if (! empty($proxyValueOptions)) {
            $this->setValueOptions($proxyValueOptions);
        }

        return $this->valueOptions;
    }
}
