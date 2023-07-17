<?php

declare(strict_types=1);

namespace DoctrineModule\Validator\Service;

use DoctrineModule\Validator\ObjectExists;
use Psr\Container\ContainerInterface;

final class ObjectExistsFactory extends AbstractValidatorFactory
{
    protected string $validatorClass = ObjectExists::class;

    /**
     * {@inheritDoc}
     */
    public function __invoke(ContainerInterface $container, $requestedName, array|null $options = null)
    {
        $repository = $this->getRepository($container, $options);

        return new ObjectExists($this->merge($options, [
            'object_repository' => $repository,
            'fields'            => $this->getFields($options),
        ]));
    }
}
