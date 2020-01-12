<?php

namespace DoctrineModule\Component\Console\Input;

use Laminas\Console\Request;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputDefinition;

/**
 * RequestInput represents an input provided as an console request.
 *
 * @license MIT
 * @author Aleksandr Sandrovskiy <a.sandrovsky@gmail.com>
 */
class RequestInput extends ArgvInput
{
    /**
     * Constructor
     *
     * @param Request $request
     * @param \Symfony\Component\Console\Input\InputDefinition $definition
     */
    public function __construct(Request $request, InputDefinition $definition = null)
    {
        $parameters = [
            null,
        ];

        foreach ($request->getParams() as $key => $param) {
            if (is_numeric($key)) {
                $parameters[] = $param;
            }
        }

        parent::__construct($parameters, $definition);
    }
}
