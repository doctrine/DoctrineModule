<?php
namespace SpiffyDoctrine\Annotation\Filter;
use Doctrine\Common\Annotations\Annotation;

/** Zend Validator SuperClass */
class Zend extends Annotation
{
}

/** @Annotation */
final class Alnum extends Zend
{
    public $class = 'Zend\Filter\Alnum';
}

/** @Annotation */
final class Alpha extends Zend
{
    public $class = 'Zend\Filter\Alpha';
}

/** @Annotation */
final class BaseName extends Zend
{
    public $class = 'Zend\Filter\BaseName';
}

/** @Annotation */
final class Boolean extends Zend
{
    public $class = 'Zend\Filter\Boolean';
}

/** @Annotation */
final class Callback extends Zend
{
    public $class = 'Zend\Filter\Callback';
}

/** @Annotation */
final class Compress extends Zend
{
    public $class = 'Zend\Filter\Compress';
}

/** @Annotation */
final class Decompress extends Zend
{
    public $class = 'Zend\Filter\Decompress';
}

/** @Annotation */
final class Digits extends Zend
{
    public $class = 'Zend\Filter\Digits';
}

/** @Annotation */
final class Dir extends Zend
{
    public $class = 'Zend\Filter\Dir';
}

/** @Annotation */
final class Encrypt extends Zend
{
    public $class = 'Zend\Filter\Encrypt';
}

/** @Annotation */
final class Decrypt extends Zend
{
    public $class = 'Zend\Filter\Decrypt';
}

/** @Annotation */
final class HtmlEntities extends Zend
{
    public $class = 'Zend\Filter\HtmlEntities';
}

/** @Annotation */
final class Int extends Zend
{
    public $class = 'Zend\Filter\Int';
}

/** @Annotation */
final class LocalizedToNormalized extends Zend
{
    public $class = 'Zend\Filter\LocalizedToNormalized';
}

/** @Annotation */
final class NormalizedToLocalized extends Zend
{
    public $class = 'Zend\Filter\NormalizedToLocalized';
}

/** @Annotation */
final class Null extends Zend
{
    public $class = 'Zend\Filter\Null';
}

/** @Annotation */
final class PregReplace extends Zend
{
    public $class = 'Zend\Filter\PregReplace';
}

/** @Annotation */
final class RealPath extends Zend
{
    public $class = 'Zend\Filter\RealPath';
}

/** @Annotation */
final class StringToLower extends Zend
{
    public $class = 'Zend\Filter\StringToLower';
}

/** @Annotation */
final class StringToUpper extends Zend
{
    public $class = 'Zend\Filter\StringToUpper';
}

/** @Annotation */
final class StringTrim extends Zend
{
    public $class = 'Zend\Filter\StringTrim';
}

/** @Annotation */
final class StripNewLines extends Zend
{
    public $class = 'Zend\Filter\StripNewLines';
}

/** @Annotation */
final class StripTags extends Zend
{
    public $class = 'Zend\Filter\StripTags';
}
