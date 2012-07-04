<?php

namespace DoctrineModule\Options;

use Zend\Stdlib\AbstractOptions;

class Configuration extends AbstractOptions
{
    /**
     * Set the cache key for the result cache. Cache key
     * is assembled as "doctrine.cache.{key}" and pulled from
     * service locator.
     *
     * @var string
     */
    protected $resultCache = 'array';

    /**
     * Set the class name of the SQL Logger, or null, to disable.
     *
     * @var string
     */
    protected $sqlLogger = null;

    /**
     * Keys must be the name of the type identifier and value is
     * the class name of the Type
     *
     * @var array
     */
    protected $types = array();

    /**
     * @param string $resultCache
     */
    public function setResultCache($resultCache)
    {
        $this->resultCache = $resultCache;
    }

    /**
     * @return string
     */
    public function getResultCache()
    {
        return "doctrine.cache.{$this->resultCache}";
    }

    /**
     * @param string $sqlLogger
     */
    public function setSqlLogger($sqlLogger)
    {
        $this->sqlLogger = $sqlLogger;
    }

    /**
     * @return string
     */
    public function getSqlLogger()
    {
        return $this->sqlLogger;
    }

    /**
     * @param string $types
     */
    public function setTypes(array $types)
    {
        $this->types = $types;
    }

    /**
     * @return string
     */
    public function getTypes()
    {
        return $this->types;
    }
}