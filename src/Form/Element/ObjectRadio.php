<?php

declare(strict_types=1);

namespace DoctrineModule\Form\Element;

use Laminas\Form\Element\Radio as RadioElement;

class ObjectRadio extends RadioElement
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
        return parent::setValue($this->getProxy()->getValue($value));
    }

    /**
     * {@inheritDoc}
     *
     * @return array<int<0, max>|string, array{attributes?: array<string, scalar|null>, disabled?: bool, label: non-empty-string, label_attributes?: array<string, scalar|null>, selected?: bool, value: non-empty-string}|string>
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
