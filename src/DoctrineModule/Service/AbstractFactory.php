<?php

namespace DoctrineModule\Service;

use RuntimeException;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

abstract class AbstractFactory implements FactoryInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var \Zend\Stdlib\Options
     */
    protected $options;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets options from configuration based on name.
     *
     * @param ServiceLocatorInterface $sl
     * @param string $key
     * @param null|string $name
     * @return \Zend\Stdlib\Options
     * @throws \RuntimeException
     */
    public function getOptions(ServiceLocatorInterface $sl, $key, $name = null)
    {
        if ($name === null) {
            $name = $this->getName();
        }

        $options = $sl->get('Configuration');
        $options = $options['doctrine'];
        $options = isset($options[$key][$name]) ? $options[$key][$name] : null;

        if (null === $options) {
            throw new RuntimeException(sprintf(
                'Options with name "%s" could not be found in "doctrine.%s".',
                $name,
                $key
            ));
        }

        $optionsClass = $this->getOptionsClass();
        return new $optionsClass($options);
    }

    /**
     * Get the class name of the options associated with this factory.
     *
     * @abstract
     * @return string
     */
    abstract public function getOptionsClass();
}