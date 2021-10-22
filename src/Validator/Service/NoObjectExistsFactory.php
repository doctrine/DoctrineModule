<?php

declare(strict_types=1);

namespace DoctrineModule\Validator\Service;

use DoctrineModule\Validator\NoObjectExists;
use Interop\Container\ContainerInterface;

/**
 * Factory for creating NoObjectExists instances
 *
 * @link    http://www.doctrine-project.org/
 */
class NoObjectExistsFactory extends AbstractValidatorFactory
{
    /** @var string */
    protected $validatorClass = NoObjectExists::class;

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
