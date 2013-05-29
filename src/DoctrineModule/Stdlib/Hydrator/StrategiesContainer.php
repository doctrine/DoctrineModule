<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Marco
 */

namespace DoctrineModule\Stdlib\Hydrator;


use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\Strategy\CompositeStrategy;
use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;
use Zend\Stdlib\Hydrator\StrategyEnabledInterface;

/**
 * @internal don't use this!
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 */
class StrategiesContainer implements StrategyEnabledInterface
{
    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $objectManager;

    protected $metadata;

    /**
     * @var StrategyInterface[]
     */
    protected $baseStrategies = array();

    /**
     * @var StrategyInterface[]
     */
    protected $customStrategies = array();

    /**
     * @var StrategyInterface[]
     */
    protected $strategies = array();

    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function prepare($object)
    {
        throw new \BadMethodCallException('TBD');
        //$this->metadata = $this->objectManager->getClassMetadata(get_class($object));

        // @todo load strategies here
    }

    /**
     * {@inheritDoc}
     */
    public function addStrategy($name, StrategyInterface $strategy)
    {
        $this->removeStrategy($name);
        $this->customStrategies[$name] = $strategy;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getStrategy($name)
    {
        if (isset($this->strategies[$name])) {
            return $this->strategies[$name];
        }

        if (isset($this->baseStrategies[$name]) && isset($this->customStrategies[$name])) {
            return $this->strategies[$name] = new CompositeStrategy(
                $this->baseStrategies[$name],
                $this->customStrategies[$name]
            );
        }

        if (isset($this->customStrategies[$name])) {
            return $this->customStrategies[$name];
        }

        if (isset($this->baseStrategies[$name])) {
            return $this->baseStrategies[$name];
        }

        if (isset($this->customStrategies['*'])) {
            $this->customStrategies['*'];
        }

        throw new \Zend\Stdlib\Exception\InvalidArgumentException(sprintf(
            '%s: no strategy by name of "%s", and no wildcard strategy present',
            __METHOD__,
            $name
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function hasStrategy($name)
    {
        return array_key_exists($name, $this->strategies)
            || array_key_exists($name, $this->baseStrategies)
            || array_key_exists($name, $this->customStrategies)
            || array_key_exists('*', $this->customStrategies);
    }

    /**
     * {@inheritDoc}
     */
    public function removeStrategy($name)
    {
        unset($this->strategies[$name], $this->customStrategies[$name]);

        return $this;
    }
}
