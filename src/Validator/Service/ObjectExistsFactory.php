<?php

declare(strict_types=1);

namespace DoctrineModule\Validator\Service;

use DoctrineModule\Validator\ObjectExists;
use Psr\Container\ContainerInterface;

final class ObjectExistsFactory extends AbstractValidatorFactory
{
    protected string $validatorClass = ObjectExists::class;

    public function __invoke(ContainerInterface $container, string $requestedName, array|null $options = null): mixed
    {
        $repository = $this->getRepository($container, $options);

        return new ObjectExists($this->merge($options, [
            'object_repository' => $repository,
            'fields'            => $this->getFields($options),
        ]));
    }
}
