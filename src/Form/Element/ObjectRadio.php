<?php

declare(strict_types=1);

namespace DoctrineModule\Form\Element;

use Laminas\Form\Element\MultiCheckbox;
use Laminas\Form\Element\Radio as RadioElement;

/** @psalm-import-type ValueOptionSpec from MultiCheckbox */
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
     * @return ValueOptionSpec
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
