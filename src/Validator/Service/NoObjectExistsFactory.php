<?php

declare(strict_types=1);

namespace DoctrineModule\Validator\Service;

use DoctrineModule\Validator\NoObjectExists;
use Interop\Container\ContainerInterface;

final class NoObjectExistsFactory extends AbstractValidatorFactory
{
    protected string $validatorClass = NoObjectExists::class;

    /**
     * {@inheritDoc}
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $repository = $this->getRepository($container, $options);

        return new NoObjectExists($this->merge($options, [
            'object_repository' => $repository,
            'fields'            => $this->getFields($options),
        ]));
    }
}
