<?php

namespace DoctrineModule\Stdlib\Hydrator\Strategy;


use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;

class CompositeStrategy implements StrategyInterface
{
    protected $baseStrategy;

    protected $additionalStrategy;

    public function __construct(
        StrategyInterface $baseStrategy,
        StrategyInterface $additionalStrategy
    ) {
        $this->baseStrategy       = $baseStrategy;
        $this->additionalStrategy = $additionalStrategy;
    }

    /**
     * {@inheritDoc}
     */
    public function extract($value, $object = null)
    {
        return $this->additionalStrategy->extract(
            $this->baseStrategy->extract($value, $object),
            $object
        );
    }

    /**
     * {@inheritDoc}
     */
    public function hydrate($value, $data = null)
    {
        return $this->additionalStrategy->hydrate(
            $this->baseStrategy->hydrate($value, $data),
            $data
        );
    }
}
