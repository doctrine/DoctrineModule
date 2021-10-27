<?php

declare(strict_types=1);

namespace DoctrineModule\Validator\Service;

use DoctrineModule\Validator\ObjectExists;
use Interop\Container\ContainerInterface;

/**
 * Factory for creating ObjectExists instances
 *
 * @link    http://www.doctrine-project.org/
 */
class ObjectExistsFactory extends AbstractValidatorFactory
{
    /** @var string */
    protected $validatorClass = ObjectExists::class;

    /**
     * {@inheritDoc}
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $repository = $this->getRepository($container, $options);

        return new ObjectExists($this->merge($options, [
            'object_repository' => $repository,
            'fields'            => $this->getFields($options),
        ]));
    }
}
