<?php

namespace DoctrineModule\Mvc\Router\Console;

use Symfony\Component\Console\Application;
use Laminas\Mvc\Console\Router\RouteInterface;
use Laminas\Stdlib\RequestInterface as Request;
use Laminas\Router\RouteMatch;
use Laminas\Console\Request as ConsoleRequest;

/**
 * Route matching commands in Symfony CLI
 *
 * @license MIT
 * @author Aleksandr Sandrovskiy <a.sandrovsky@gmail.com>
 */
class SymfonyCli implements RouteInterface
{
    /**
     * @var \Symfony\Component\Console\Application
     */
    protected $cliApplication;

    /**
     * Default values.
     *
     * @var array
     */
    protected $defaults;

    /**
     * Constructor
     *
     * @param \Symfony\Component\Console\Application $cliApplication
     * @param array                                  $defaults
     */
    public function __construct(Application $cliApplication, array $defaults = [])
    {
        $this->cliApplication = $cliApplication;
        $this->defaults       = $defaults;
    }

    /**
     * {@inheritDoc}
     */
    public function match(Request $request)
    {
        if (! $request instanceof ConsoleRequest) {
            return null;
        }

        $params = $request->getParams()->toArray();

        if (! isset($params[0]) || ! $this->cliApplication->has($params[0])) {
            return null;
        }

        return new RouteMatch($this->defaults);
    }

    /**
     * Disabled.
     *
     * {@inheritDoc}
     *
     * @throws \BadMethodCallException this method is disabled
     */
    public function assemble(array $params = [], array $options = [])
    {
        throw new \BadMethodCallException('Unsupported');
    }

    /**
     * {@inheritDoc}
     */
    public function getAssembledParams()
    {
        return [];
    }

    /**
     * Disabled.
     *
     * {@inheritDoc}
     *
     * @throws \BadMethodCallException this method is disabled
     */
    public static function factory($options = [])
    {
        throw new \BadMethodCallException('Unsupported');
    }
}
