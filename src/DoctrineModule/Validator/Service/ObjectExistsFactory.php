<?php


namespace DoctrineModule\Validator\Service;

use Interop\Container\ContainerInterface;
use DoctrineModule\Validator\ObjectExists;

/**
 * Factory for creating ObjectExists instances
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @since   1.3.0
 * @author  Fabian Grutschus <f.grutschus@lubyte.de>
 */
class ObjectExistsFactory extends AbstractValidatorFactory
{
    protected $validatorClass = ObjectExists::class;

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $container = $this->container($container);

        $repository = $this->getRepository($container, $options);

        $validator = new ObjectExists($this->merge($options, [
            'object_repository' => $repository,
            'fields'            => $this->getFields($options),
        ]));

        return $validator;
    }
}
