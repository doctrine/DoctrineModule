<?php

declare(strict_types=1);

namespace DoctrineModule\Service;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\Stdlib\AbstractOptions;
use RuntimeException;

use function sprintf;

/**
 * Base ServiceManager factory to be extended
 */
// phpcs:disable SlevomatCodingStandard.Classes.SuperfluousAbstractClassNaming
abstract class AbstractFactory implements FactoryInterface
{
// phpcs:enable SlevomatCodingStandard.Classes.SuperfluousAbstractClassNaming
    /**
     * Would normally be set to orm | odm
     *
     * @var string
     */
    protected $mappingType;

    /** @var string */
    protected $name;

    /** @var AbstractOptions */
    protected $options;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Would normally be set to orm | odm
     */
    public function getMappingType(): string
    {
        return (string) $this->mappingType;
    }

    /**
     * Gets options from configuration based on name.
     *
     * @throws RuntimeException
     */
    public function getOptions(ContainerInterface $container, string $key, ?string $name = null): AbstractOptions
    {
        if ($name === null) {
            $name = $this->getName();
        }

        $options     = $container->get('config');
        $options     = $options['doctrine'];
        $mappingType = $this->getMappingType();
        if ($mappingType) {
            $options = $options[$mappingType];
        }

        $options = $options[$key][$name] ?? null;

        if ($options === null) {
            throw new RuntimeException(
                sprintf(
                    'Options with name "%s" could not be found in "doctrine.%s".',
                    $name,
                    $key
                )
            );
        }

        $optionsClass = $this->getOptionsClass();

        return new $optionsClass($options);
    }

    /**
     * Get the class name of the options associated with this factory.
     *
     * @abstract
     */
    abstract public function getOptionsClass(): string;
}
