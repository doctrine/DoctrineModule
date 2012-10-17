<?php

namespace DoctrineModule\Form\Element;

use RuntimeException;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Form\Element\Proxy;
use Zend\Form\Element\Select as SelectElement;
use Zend\Form\Form;

class ObjectSelect extends SelectElement
{
    /**
     * @var Proxy
     */
    protected $proxy;

    /**
     * @return Proxy
     */
    public function getProxy()
    {
        if (null === $this->proxy) {
            $this->proxy = new Proxy();
        }
        return $this->proxy;
    }

    /**
     * @param  array|\Traversable $options
     * @return ObjectSelect
     */
    public function setOptions($options)
    {
        $this->getProxy()->setOptions($options);
        return parent::setOptions($options);
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
        if (empty($this->valueOptions)) {
            $this->setValueOptions($this->getProxy()->getValueOptions());
        }
        return $this->valueOptions;
    }
}
