<?php

namespace DoctrineModule\Controller\Plugin;

use Doctrine\Common\Persistence\ObjectManager;
use Zend\Mvc\Controller\Plugin\Params as ZendParamsPlugin;
use Zend\Mvc\Controller\Plugin\PluginInterface;
use Zend\Stdlib\DispatchableInterface;

/**
 * This class has the same methods as the standard Params controller plugin (which it wraps)
 * Except it returns entities, which are fetched by id from the parameters
 */
class Params implements PluginInterface
{
    /* @var ObjectManager */
    private $objectManager;

    /* @var ZendParamsPlugin */
    private $paramPlugin;

    public function __construct(ObjectManager $objectManager, ZendParamsPlugin $paramPlugin)
    {
        $this->objectManager = $objectManager;
        $this->paramPlugin = $paramPlugin;
    }

    public function setController(DispatchableInterface $controller)
    {
        $this->paramPlugin->setController($controller);
    }

    public function getController()
    {
        return $this->paramPlugin->getController();
    }

    /**
     * @param string $className
     * @param string ?$parameterName
     * @param ?$default
     * @return $this|object|$default
     */
    public function __invoke($className = null, $parameterName = null, $default = null)
    {
        if (func_num_args() === 0) {
            return $this;
        }
        return $this->fromRoute($className, $parameterName, $default);
    }

    public function fromFiles()
    {
        throw new \BadMethodCallException('Not implemented');
    }

    public function fromHeader()
    {
        throw new \BadMethodCallException('Not implemented');
    }

    /**
     * Fetch object by id from a POST parameter.
     *
     * @param string $className Full entity classname to retrieve
     * @param ?string $parameterName if the parameter name does not equal the non-namespaced Class name.
     * @return object|$default
     */
    public function fromPost($className, $parameterName = null, $default = null)
    {
        if (!$className) {
            throw new \InvalidArgumentException('Argument "classname" is required');
        }

        $parameterName = $parameterName ?: $this->classNameToParamName($className);
        $value = $this->paramPlugin->fromPost($parameterName);

        return $this->paramValueToObject($className, $value, $default);
    }

    /**
     * Fetch object by id from a query parameter.
     *
     * @param string $className Full entity classname to retrieve
     * @param ?string $parameterName if the parameter name does not equal the non-namespaced Class name.
     * @param ?mixed default
     * @return object|$default
     */
    public function fromQuery($className, $parameterName = null, $default = null)
    {
        if (!$className) {
            throw new \InvalidArgumentException('Argument "classname" is required');
        }

        $parameterName = $parameterName ?: $this->classNameToParamName($className);
        $value = $this->paramPlugin->fromQuery($parameterName);

        return $this->paramValueToObject($className, $value, $default);
    }

    /**
     * Fetch object by id from a route parameter.
     *
     * @param string $className Full entity classname to retrieve
     * @param ?string $parameterName if the parameter name does not equal the non-namespaced Class name.
     * @param ?mixed default
     * @return object|$default
     */
    public function fromRoute($className = null, $parameterName = null, $default = null)
    {
        $parameterName = $parameterName ?: $this->classNameToParamName($className);
        $value = $this->paramPlugin->fromRoute($parameterName);

        return $this->paramValueToObject($className, $value, $default);
    }

    protected function classNameToParamName($className)
    {
        return strtolower(substr($className, strrpos($className, '\\') + 1));
    }

    protected function paramValueToObject($className, $value, $default)
    {
        if ($value === null) {
            return $default;
        } elseif (ctype_digit($value)) {
            $entity = $this->objectManager->find($className, $value);
            return $entity ? $entity : $default;
        } else {
            // id is likely a non-numeric string, or an array
            error_log('invalid user input found in parameters');
            return $default;
        }
    }
}