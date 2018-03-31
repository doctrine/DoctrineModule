<?php

namespace DoctrineModuleTest\Stdlib\Hydrator\Asset;

use DateTime;

class SimpleEntityWithMultiWordDateTime
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var DateTime
     */
    protected $multiWordDate;

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setMultiWordDate(DateTime $date = null)
    {
        $this->multiWordDate = $date;
    }

    public function getMultiWordDate()
    {
        return $this->multiWordDate;
    }
}
