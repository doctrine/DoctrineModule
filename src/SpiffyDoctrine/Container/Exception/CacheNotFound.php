<?php
namespace SpiffyDoctrine\Container\Exception;

class CacheNotFound extends \InvalidArgumentException
{
    public function __construct($name)
    {
        parent::__construct(
            sprintf('Cache with name "%s" could not be found in configuration.', $name)
        );
    }
}
