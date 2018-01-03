<?php

namespace DoctrineModule\Options;

use Zend\Stdlib\AbstractOptions;

/**
 * EventManager options
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Kyle Spraggs <theman@spiffyjr.me>
 */
class EventManager extends AbstractOptions
{
    /**
     * An array of subscribers. The array can contain the FQN of the
     * class to instantiate OR a string to be located with the
     * service locator.
     *
     * @var array
     */
    protected $subscribers = [];

    /**
     * @param  array $subscribers
     * @return self
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
