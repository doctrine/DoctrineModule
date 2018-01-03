<?php

namespace DoctrineModule\Options;

use Zend\Stdlib\AbstractOptions;

/**
 * MappingDriver options
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Kyle Spraggs <theman@spiffyjr.me>
 */
class Driver extends AbstractOptions
{
    /**
     * The class name of the Driver.
     *
     * @var string
     */
    protected $class;

    /**
     * All drivers (except DriverChain) require paths to work on. You
     * may set this value as a string (for a single path) or an array
     * for multiple paths.
     *
     * @var array
     */
    protected $paths = [];

    /**
     * Set the cache key for the annotation cache. Cache key
     * is assembled as "doctrine.cache.{key}" and pulled from
     * service locator. This option is only valid for the
     * AnnotationDriver.
     *
     * @var string
     */
    protected $cache = 'array';

    /**
     * Set the file extension to use. This option is only
     * valid for FileDrivers (XmlDriver, YamlDriver, PHPDriver, etc).
     *
     * @var string|null
     */
    protected $extension = null;

    /**
     * Set the driver keys to use which are assembled as
     * "doctrine.driver.{key}" and pulled from the service
     * locator. This option is only valid for DriverChain.
     *
     * @var array
     */
    protected $drivers = [];

    /**
     * @param string $cache
     */
    public function setCache($cache)
    {
        $this->cache = $cache;
    }

    /**
     * @return string
     */
    public function getCache()
    {
        return "doctrine.cache.{$this->cache}";
    }

    /**
     * @param string $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param array $drivers
     */
    public function setDrivers($drivers)
    {
        $this->drivers = $drivers;
    }

    /**
     * @return array
     */
    public function getDrivers()
    {
        return $this->drivers;
    }

    /**
     * @param null $extension
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;
    }

    /**
     * @return string|null
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * @param array $paths
     */
    public function setPaths($paths)
    {
        $this->paths = $paths;
    }

    /**
     * @return array
     */
    public function getPaths()
    {
        return $this->paths;
    }
}
