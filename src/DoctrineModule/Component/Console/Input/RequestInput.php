<?php

declare(strict_types=1);

namespace DoctrineModule\Component\Console\Input;

use Laminas\Console\Request;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputDefinition;
use function is_numeric;

/**
 * RequestInput represents an input provided as an console request.
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
