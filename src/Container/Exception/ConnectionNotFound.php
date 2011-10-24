<?php
namespace SpiffyDoctrine\Container\Exception;

class ConnectionNotFound extends \InvalidArgumentException
{
    public function __construct($name)
    {
        parent::__construct(
            sprintf('Connection with name "%s" could not be found in configuration.', $name)
        );
    }
}
