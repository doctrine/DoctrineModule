<?php
namespace SpiffyDoctrine\ORM;

use Doctrine\ORM\Configuration as ORMConfiguration,
    SpiffyDoctrine\ORM\Configuration\CustomNumericFunction as NumericFunction,
    SpiffyDoctrine\ORM\Configuration\CustomStringFunction as StringFunction,
    SpiffyDoctrine\ORM\Configuration\CustomDatetimeFunction as DatetimeFunction,
    SpiffyDoctrine\ORM\Configuration\CustomHydrationMode as HydrationMode,
    SpiffyDoctrine\ORM\Configuration\NamedQuery,
    SpiffyDoctrine\ORM\Configuration\NamedNativeQuery;

/**
 * Configuration object compatible with Zend\Di. Should not be necessary anymore when following works:
 * @link https://github.com/ralphschindler/Zend_DI-Examples/blob/master/example-15.php
 * 
 * @author Marco Pivetta <ocramius@gmail.com>
 */
class Configuration extends ORMConfiguration {
    
    /**
     *
     * @param NumericFunction $function 
     */
    public function addNumericFunction(NumericFunction $numericFunction)
    {
        parent::addCustomNumericFunction(
            $numericFunction->getName(),
            $numericFunction->getClassName()
        );
    }
    
    /**
     *
     * @param StringFunction $function 
     */
    public function addStringFunction(StringFunction $stringFunction)
    {
        parent::addCustomStringFunction(
            $stringFunction->getName(),
            $stringFunction->getClassName()
        );
    }
    
    /**
     *
     * @param DatetimeFunction $function 
     */
    public function addDatetimeFunction(DatetimeFunction $datetimeFunction)
    {
        parent::addCustomDatetimeFunction(
            $datetimeFunction->getName(),
            $datetimeFunction->getClassName()
        );
    }
    
    /**
     *
     * @param HydrationMode $hydrationMode 
     */
    public function addHydrationMode(HydrationMode $hydrationMode)
    {
        parent::addCustomHydrationMode(
            $hydrationMode->getModeName(),
            $hydrationMode->getHydrator()
        );
    }
    
    /**
     *
     * @param NamedQuery $namedQuery 
     */
    public function addQuery(NamedQuery $namedQuery)
    {
        parent::addNamedQuery($namedQuery->getName(), $namedQuery->getDql());
    }
    
    /**
     *
     * @param NamedNativeQuery $namedNativeQuery 
     */
    public function addNativeQuery(NamedNativeQuery $namedNativeQuery)
    {
        parent::addNamedNativeQuery(
            $namedNativeQuery->getName(),
            $namedNativeQuery->getSql(),
            $namedNativeQuery->getRsm()
        );
    }
    
}