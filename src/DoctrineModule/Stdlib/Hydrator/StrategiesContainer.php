<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Marco
 */

namespace DoctrineModule\Stdlib\Hydrator;


use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;
use Zend\Stdlib\Hydrator\StrategyEnabledInterface;

/**
 * @internal don't use this!
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 */
class StrategiesContainer implements StrategyEnabledInterface
{
    protected $objectManager;
    protected $metadata;

    protected $strategies = array();

    protected $fieldStrategies;

    public function __construct(ObjectManager $objectManager/*, ClassMetadata $metadata*/)
    {
        //$this->metadata = $metadata;
    }

    /**
     * {@inheritDoc}
     */
    public function addStrategy($name, StrategyInterface $strategy)
    {
        $this->strategies[$name] = $strategy;

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

        if (isset($this->strategies['*'])) {
            $this->strategies['*'];
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
        return array_key_exists($name, $this->strategies) || array_key_exists('*', $this->strategies);
    }

    /**
     * {@inheritDoc}
     */
    public function removeStrategy($name)
    {
        unset($this->strategies[$name]);

        return $this;
    }
}
