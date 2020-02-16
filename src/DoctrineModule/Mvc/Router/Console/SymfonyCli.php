<?php

declare(strict_types=1);

namespace DoctrineModule\Mvc\Router\Console;

use BadMethodCallException;
use Laminas\Console\Request as ConsoleRequest;
use Laminas\Mvc\Console\Router\RouteInterface;
use Laminas\Router\RouteMatch;
use Laminas\Stdlib\RequestInterface as Request;
use Symfony\Component\Console\Application;

/**
 * Route matching commands in Symfony CLI
 */
class SymfonyCli implements RouteInterface
{
    /** @var Application */
    protected $cliApplication;

    /**
     * Default values.
     *
     * @var mixed[]
     */
    protected $defaults;

    /**
     * Constructor
     *
     * @param mixed[] $defaults
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
     * @throws BadMethodCallException this method is disabled.
     */
    public function assemble(array $params = [], array $options = [])
    {
        throw new BadMethodCallException('Unsupported');
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
     * @throws BadMethodCallException this method is disabled.
     */
    public static function factory($options = [])
    {
        throw new BadMethodCallException('Unsupported');
    }
}
