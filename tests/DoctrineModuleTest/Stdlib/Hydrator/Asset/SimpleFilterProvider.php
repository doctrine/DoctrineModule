<?php

namespace DoctrineModuleTest\Stdlib\Hydrator\Asset;

use Zend\Stdlib\Hydrator\Filter\FilterProviderInterface;

class SimpleFilterProvider implements FilterProviderInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $password;

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getFilter()
    {
        return new SimpleFilter();
    }
}
