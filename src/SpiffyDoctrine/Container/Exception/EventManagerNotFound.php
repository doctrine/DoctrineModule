<?php
namespace SpiffyDoctrine\Container\Exception;

class EventManagerNotFound extends \InvalidArgumentException
{
    public function __construct($name)
    {
        parent::__construct(
            sprintf('EventManager with name "%s" could not be found in configuration.', $name)
        );
    }
}
