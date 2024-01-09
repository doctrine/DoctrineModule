<?php

declare(strict_types=1);

namespace DoctrineModule\Form\Element;

use Laminas\Form\Element\MultiCheckbox;
use Laminas\Stdlib\ArrayUtils;
use Traversable;

use function array_map;
use function is_array;

class ObjectMultiCheckbox extends MultiCheckbox
{
    use GetProxy;

    /**
     * @param iterable<mixed> $options
     *
     * @return $this
     */
    public function setOptions(iterable $options): self
    {
        $this->getProxy()->setOptions($options);

        return parent::setOptions($options);
    }

    /** @return $this */
    public function setOption(string $key, mixed $value): self
    {
        $this->getProxy()->setOptions([$key => $value]);

        return parent::setOption($key, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function setValue($value)
    {
        if ($value instanceof Traversable) {
            $value = ArrayUtils::iteratorToArray($value);
        } elseif ($value === null) {
            return parent::setValue([]);
        } elseif (! is_array($value)) {
            $value = (array) $value;
        }

        return parent::setValue(array_map([$this->getProxy(), 'getValue'], $value));
    }

    /**
     * {@inheritDoc}
     *
     * @return array<int<0, max>|string, array{attributes?: array<string, null|scalar>, disabled?: bool, label: non-empty-string, label_attributes?: array<string, null|scalar>, selected?: bool, value: non-empty-string}|string>
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
