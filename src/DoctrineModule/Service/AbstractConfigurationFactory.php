<?php

namespace DoctrineModule\Service;

use RuntimeException;
use Doctrine\DBAL\Configuration;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

abstract class AbstractConfigurationFactory implements FactoryInterface
{
    /**
     * @var string
     */
    protected $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function setupDBALConfiguration(ServiceLocatorInterface $serviceLocator, Configuration $config)
    {
        $options = $this->getOptions($serviceLocator);

        $config->setResultCacheImpl($serviceLocator->get($options->resultCache));
        $config->setSQLLogger($options->sqlLogger);
    }

    public function getOptions(ServiceLocatorInterface $serviceLocator)
    {
        $options = $serviceLocator->get('Configuration');
        $options = $options['doctrine'];
        $options = isset($options['configuration'][$this->name]) ? $options['configuration'][$this->name] : null;

        if (null === $options) {
            throw new RuntimeException(sprintf(
                'Configuration with name "%s" could not be found in "doctrine.configuration".',
                $this->name
            ));
        }

        $optionsClass = $this->getOptionsClass();
        return new $optionsClass($options);
    }

    abstract protected function getOptionsClass();
}