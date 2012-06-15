<?php

namespace DoctrineModule\Options;

use Zend\Stdlib\Options;

class Configuration extends Options
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
}