<?php

declare(strict_types=1);

namespace DoctrineModule\Component\Console\Input;

use Laminas\Console\Request;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputDefinition;

use function class_exists;
use function is_numeric;
use function sprintf;
use function trigger_error;

if (! class_exists(Request::class)) {
    trigger_error(sprintf(
        'Using %s requires the package laminas/laminas-mvc-console, which is currently not installed.',
        RequestInput::class
    ));

    return;
}

/**
 * RequestInput represents an input provided as an console request.
 *
 * @deprecated 4.2.0 Usage of laminas/laminas-mvc-console is deprecated, integration will be removed in 5.0.0.
 *                   Please use ./vendor/bin/doctrine-module instead.
 */
class RequestInput extends ArgvInput
{
    /**
     * Constructor
     */
    public function __construct(Request $request, ?InputDefinition $definition = null)
    {
        $parameters = [null];

        foreach ($request->getParams() as $key => $param) {
            if (! is_numeric($key)) {
                continue;
            }

            $parameters[] = $param;
        }

        parent::__construct($parameters, $definition);
    }
}
