<?php
namespace SpiffyDoctrine\Annotation\Validator;
use Doctrine\Common\Annotations\Annotation;

/** Zend Validator SuperClass */
class Zend extends Annotation
{
    public $breakChain = false;
}

/** @Annotation */
final class Alpha extends Zend
{
    public $class = 'Zend\Validator\Alpha';
}

/** @Annotation */
final class Barcode extends Zend
{
    public $class = 'Zend\Validator\Barcode';
}

/** @Annotation */
final class Between extends Zend
{
    public $class = 'Zend\Validator\Between';
}

/** @Annotation */
final class Callback extends Zend
{
    public $class = 'Zend\Validator\Callback';
}

/** @Annotation */
final class CreditCard extends Zend
{
    public $class = 'Zend\Validator\CreditCard';
}

/** @Annotation */
final class Ccnum extends Zend
{
    public $class = 'Zend\Validator\Ccnum';
}

/** @Annotation */
final class Date extends Zend
{
    public $class = 'Zend\Validator\Date';
}

/** @Annotation */
final class Digits extends Zend
{
    public $class = 'Zend\Validator\Digits';
}

/** @Annotation */
final class EmailAddress extends Zend
{
    public $class = 'Zend\Validator\EmailAddress';
}

/** @Annotation */
final class Float extends Zend
{
    public $class = 'Zend\Validator\Float';
}

/** @Annotation */
final class GreaterThan extends Zend
{
    public $class = 'Zend\Validator\GreaterThan';
}

/** @Annotation */
final class Hex extends Zend
{
    public $class = 'Zend\Validator\Hex';
}

/** @Annotation */
final class Hostname extends Zend
{
    public $class = 'Zend\Validator\Hostname';
}

/** @Annotation */
final class Iban extends Zend
{
    public $class = 'Zend\Validator\Iban';
}

/** @Annotation */
final class Identical extends Zend
{
    public $class = 'Zend\Validator\Identical';
}

/** @Annotation */
final class InArray extends Zend
{
    public $class = 'Zend\Validator\InArray';
}

/** @Annotation */
final class Int extends Zend
{
    public $class = 'Zend\Validator\Int';
}

/** @Annotation */
final class Isbn extends Zend
{
    public $class = 'Zend\Validator\Isbn';
}

/** @Annotation */
final class LessThan extends Zend
{
    public $class = 'Zend\Validator\LessThan';
}

/** @Annotation */
final class NotEmpty extends Zend
{
    public $class = 'Zend\Validator\NotEmpty';
}

/** @Annotation */
final class PostCode extends Zend
{
    public $class = 'Zend\Validator\PostCode';
}

/** @Annotation */
final class StringLength extends Zend
{
    public $class = 'Zend\Validator\StringLength';
}
