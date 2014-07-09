<?php

namespace DoctrineModuleTest\Stdlib\Hydrator\Asset;

class NamingStrategyEntity
{
    /**
     * @var null|string
     */
    protected $camelCase;

    /**
     * @param $camelCase
     */
    public function __construct($camelCase = null)
    {
        $this->camelCase = $camelCase;
    }

    /**
     * @param null|string $camelCase
     */
    public function setCamelCase($camelCase)
    {
        $this->camelCase = $camelCase;
    }

    /**
     * @return null|string
     */
    public function getCamelCase()
    {
        return $this->camelCase;
    }
}
