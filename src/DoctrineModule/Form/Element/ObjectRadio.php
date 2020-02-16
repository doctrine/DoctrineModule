<?php

declare(strict_types=1);

namespace DoctrineModule\Form\Element;

use Laminas\Form\Element\Radio as RadioElement;
use Traversable;

class ObjectRadio extends RadioElement
{
    /** @var Proxy */
    protected $proxy;

    public function getProxy() : Proxy
    {
        if ($this->proxy === null) {
            $this->proxy = new Proxy();
        }

        return $this->proxy;
    }

    /**
     * @param array|Traversable $options
     *
     * {@inheritDoc}
     */
    public function setOptions($options) : self
    {
        $this->getProxy()->setOptions($options);

        return parent::setOptions($options);
    }

    /**
     * @param mixed $value
     *
     * {@inheritDoc}
     */
    public function setOption($key, $value) : self
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
     */
    public function getValueOptions()
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
