<?php
namespace SpiffyDoctrine\Container\Exception;

class EntityManagerNotFound extends \InvalidArgumentException
{
    public function __construct($name)
    {
        parent::__construct(
            sprintf('EntityManager with name "%s" could not be found in configuration.', $name)
        );
    }
}
