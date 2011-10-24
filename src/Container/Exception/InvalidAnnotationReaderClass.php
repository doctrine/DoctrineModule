<?php
namespace SpiffyDoctrine\Container\Exception;

class InvalidAnnotationReaderClass extends \InvalidArgumentException
{
    public function __construct($name)
    {
        parent::__construct('You must specify a class to use for the annotation reader');
    }
}
