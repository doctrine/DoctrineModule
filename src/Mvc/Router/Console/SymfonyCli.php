<?php

declare(strict_types=1);

namespace DoctrineModule\Mvc\Router\Console;

use BadMethodCallException;
use Laminas\Console\Request as ConsoleRequest;
use Laminas\Mvc\Console\Router\RouteInterface;
use Laminas\Router\RouteMatch;
use Laminas\Stdlib\RequestInterface as Request;
use Symfony\Component\Console\Application;

use function interface_exists;
use function sprintf;
use function trigger_error;

if (! interface_exists(RouteInterface::class)) {
    trigger_error(sprintf(
        'Using %s requires the package laminas/laminas-mvc-console, which is currently not installed.',
        SymfonyCli::class
    ));

    return;
}

/**
 * Route matching commands in Symfony CLI
 *
 * @deprecated 4.2.0 Usage of laminas/laminas-mvc-console is deprecated, integration will be removed in 5.0.0.
 *                   Please use ./vendor/bin/doctrine-module instead.
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
