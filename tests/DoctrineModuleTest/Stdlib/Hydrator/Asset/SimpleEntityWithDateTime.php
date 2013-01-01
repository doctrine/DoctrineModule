<?php

namespace DoctrineModuleTest\Stdlib\Hydrator\Asset;

use DateTime;

class SimpleEntityWithDateTime
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var DateTime
     */
    protected $date;

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setDate(DateTime $date)
    {
        $this->date = $date;
    }

    public function getDate()
    {
        return $this->date;
    }
}
