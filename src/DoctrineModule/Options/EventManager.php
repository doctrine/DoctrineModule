<?php

namespace DoctrineModule\Options;

use Zend\Stdlib\Options;

class EventManager extends Options
{
    /**
     * An array of subscribers. The array can contain the FQN of the
     * class to instantiate OR a string to be located with the
     * service locator.
     *
     * @var array
     */
    protected $subscribers = array();

    /**
     * @param array $subscribers
     */
    public function setSubscribers($subscribers)
    {
        $this->subscribers = $subscribers;
        return $this;
    }

    /**
     * @return array
     */
    public function getSubscribers()
    {
        return $this->subscribers;
    }
}