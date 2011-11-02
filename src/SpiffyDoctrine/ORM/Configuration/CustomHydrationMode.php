<?php
namespace SpiffyDoctrine\ORM\Configuration;

/**
 * A simple lightweight ValueObject representing a custom Hydrator
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 */
class CustomHydrationMode {
    
    /**
     * Name of the custom hydration mode
     * 
     * @var type 
     */
    protected $modeName;
    
    /**
     * Classname of the custom hydrator
     * 
     * @var string
     */
    protected $hydrator;
    
    /**
     *
     * @param string $modeName
     * @param string $hydrator 
     */
    public function __construct($modeName, $hydrator)
    {
        $this->setModeName($modeName);
        $this->setHydrator($hydrator);
    }
    
    /**
     * 
     * @param string $modeName 
     */
    public function setModeName($modeName)
    {
        $this->modeName = (string) $modeName;
    }
    
    /**
     * 
     * @return string 
     */
    public function getModeName()
    {
        return $this->modeName;
    }
    
    /**
     * 
     * @param string $className 
     */
    public function setHydrator($hydrator)
    {
        $this->hydrator = (string) $hydrator;
    }
    
    /**
     * 
     * @return string
     */
    public function getHydrator()
    {
        return $this->hydrator;
    }
    
}