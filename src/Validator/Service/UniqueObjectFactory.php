<?php

declare(strict_types=1);

namespace DoctrineModule\Validator\Service;

use DoctrineModule\Validator\UniqueObject;
use Psr\Container\ContainerInterface;

final class UniqueObjectFactory extends AbstractValidatorFactory
{
    protected string $validatorClass = UniqueObject::class;

    /**
     * {@inheritDoc}
     */
    public function __invoke(ContainerInterface $container, $requestedName, array|null $options = null)
    {
        $useContext = isset($options['use_context']) ? (bool) $options['use_context'] : false;

        return new UniqueObject($this->merge($options, [
            'object_manager'    => $this->getObjectManager($container, $options),
            'use_context'       => $useContext,
            'object_repository' => $this->getRepository($container, $options),
            'fields'            => $this->getFields($options),
        ]));
    }
}
