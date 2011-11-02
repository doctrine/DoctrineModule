<?php
namespace SpiffyDoctrine\ORM\Mapping\Driver;

use Doctrine\ORM\Mapping\Driver\Driver,
    Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Provides Zend\Di compatible injection of chained drivers
 * This is a workaround for a behaviour that is unsupported by Zend\Di
 * 
 * @author Marco Pivetta <ocramius@gmail.com>
 */
class DriverChain implements Driver {
    
    /**
     * @var array
     */
    private $_drivers = array();
    
    /**
     * Add a nested driver.
     *
     * @param Driver $nestedDriver
     */
    public function addDriver(Driver $nestedDriver)
    {
        $this->_drivers[] = $nestedDriver;
    }

    /**
     * Get the array of nested drivers.
     *
     * @return array $drivers
     */
    public function getDrivers()
    {
        return $this->_drivers;
    }

    /**
     * Loads the metadata for the specified class into the provided container.
     * 
     * @param string $className
     * @param ClassMetadataInfo $metadata
     */
    public function loadMetadataForClass($className, ClassMetadataInfo $metadata)
    {
        foreach ($this->_drivers as $driver) {
            $driver->loadMetadataForClass($className, $metadata);
            return;
        }
        
        throw MappingException::classIsNotAValidEntityOrMappedSuperClass($className);
    }

    /**
     * Gets the names of all mapped classes known to this driver.
     * 
     * @return array The names of all mapped classes known to this driver.
     */
    public function getAllClassNames()
    {
        $classNames = array();
        $driverClasses = array();
        foreach ($this->_drivers AS $driver) {
            $oid = spl_object_hash($driver);
            if (!isset($driverClasses[$oid])) {
                $driverClasses[$oid] = $driver->getAllClassNames();
            }
            
            foreach ($driverClasses[$oid] AS $className) {
                $classNames[$className] = true;
            }
        }
        return array_keys($classNames);
    }

    /**
     * Whether the class with the specified name should have its metadata loaded.
     *
     * This is only the case for non-transient classes either mapped as an Entity or MappedSuperclass.
     *
     * @param string $className
     * @return boolean
     */
    public function isTransient($className)
    {
        foreach ($this->_drivers AS $driver) {
            if(!$driver->isTransient($className)) {
                return false;
            }
        }
        
        // class isTransient, i.e. not an entity or mapped superclass
        return true;
    }
    
}