<?php

namespace DoctrineModuleTest\Stdlib\Hydrator\Asset;

class SimpleEntityWithParent extends SimpleEntity
{
    /**
     * @var OneToManyEntity
     */
    protected $parent;

    /**
     * Set parent
     *
     * @param  OneToManyEntity $parent
     * @return SimpleEntityWithParent
     */
    public function setParent(OneToManyEntity $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return OneToManyEntity
     */
    public function getParent()
    {
        return $this->parent;
    }
}
