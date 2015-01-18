<?php

namespace DoctrineModuleTest\Stdlib\Hydrator\Asset;

class EmbeddableObject
{
    /**
     * @var int
     */
    protected $foo;
    /**
     * @var string
     */
    protected $bar;

    public function getFoo()
    {
        return $this->foo;
    }

    public function getBar()
    {
        return $this->bar;
    }

    public function setFoo($foo)
    {
        $this->foo = $foo;
    }

    public function setBar($bar)
    {
        $this->bar = $bar;
    }
}
