<?php

declare(strict_types=1);

namespace DoctrineModule\Form\Element;

use BadMethodCallException;
use Doctrine\Common\Collections\Collection;
use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\InflectorFactory;
use Doctrine\Persistence\ObjectManager;
use DoctrineModule\Form\Element\Exception\InvalidRepositoryResultException;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Laminas\Stdlib\ArrayUtils;
use Laminas\Stdlib\Guard\ArrayOrTraversableGuardTrait;
use ReflectionMethod;
use RuntimeException;
use Traversable;

use function array_change_key_case;
use function array_key_exists;
use function array_shift;
use function call_user_func;
use function count;
use function current;
use function gettype;
use function is_callable;
use function is_object;
use function is_string;
use function method_exists;
use function sprintf;
use function strtolower;
use function trim;

class Proxy implements ObjectManagerAwareInterface
{
    use ArrayOrTraversableGuardTrait;

    /** @var iterable<object>|null */
    protected iterable|null $objects = null;

    protected string|null $targetClass = null;

    /** @var mixed[] */
    protected array $valueOptions = [];

    /** @var mixed[] */
    protected array $findMethod = [];

    protected mixed $property = null;

    /** @var mixed[] */
    protected array $optionAttributes = [];

    /** @var callable $labelGenerator A callable used to create a label based on an item in the collection an Entity */
    protected $labelGenerator;

    protected bool|null $isMethod = null;

    protected ObjectManager|null $objectManager = null;

    protected bool $displayEmptyItem = false;

    protected string $emptyItemLabel = '';

    protected string|null $optgroupIdentifier = null;

    protected string|null $optgroupDefault = null;

    protected Inflector $inflector;

    public function __construct(Inflector|null $inflector = null)
    {
        $this->inflector = $inflector ?? InflectorFactory::create()->build();
    }

    /** @param iterable<mixed> $options */
    public function setOptions(iterable $options): void
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }

        if (isset($options['object_manager'])) {
            $this->setObjectManager($options['object_manager']);
        }

        if (isset($options['target_class'])) {
            $this->setTargetClass($options['target_class']);
        }

        if (isset($options['property'])) {
            $this->setProperty($options['property']);
        }

        if (isset($options['label_generator'])) {
            $this->setLabelGenerator($options['label_generator']);
        }

        if (isset($options['find_method'])) {
            $this->setFindMethod($options['find_method']);
        }

        if (isset($options['is_method'])) {
            $this->setIsMethod($options['is_method']);
        }

        if (isset($options['display_empty_item'])) {
            $this->setDisplayEmptyItem($options['display_empty_item']);
        }

        if (isset($options['empty_item_label'])) {
            $this->setEmptyItemLabel($options['empty_item_label']);
        }

        if (isset($options['option_attributes'])) {
            $this->setOptionAttributes($options['option_attributes']);
        }

        if (isset($options['optgroup_identifier'])) {
            $this->setOptgroupIdentifier($options['optgroup_identifier']);
        }

        if (! isset($options['optgroup_default'])) {
            return;
        }

        $this->setOptgroupDefault($options['optgroup_default']);
    }

    public function getValueOptions(): mixed
    {
        if (empty($this->valueOptions)) {
            $this->loadValueOptions();
        }

        return $this->valueOptions;
    }

    public function getObjects(): mixed
    {
        $this->loadObjects();

        return $this->objects;
    }

    /**
     * Set the label for the empty option
     */
    public function setEmptyItemLabel(string $emptyItemLabel): Proxy
    {
        $this->emptyItemLabel = $emptyItemLabel;

        return $this;
    }

    public function getEmptyItemLabel(): string
    {
        return $this->emptyItemLabel;
    }

    /** @return mixed[] */
    public function getOptionAttributes(): array
    {
        return $this->optionAttributes;
    }

    /** @param mixed[] $optionAttributes */
    public function setOptionAttributes(array $optionAttributes): void
    {
        $this->optionAttributes = $optionAttributes;
    }

    /**
     * Set a flag, whether to include the empty option at the beginning or not
     */
    public function setDisplayEmptyItem(bool $displayEmptyItem): Proxy
    {
        $this->displayEmptyItem = $displayEmptyItem;

        return $this;
    }

    public function getDisplayEmptyItem(): bool
    {
        return $this->displayEmptyItem;
    }

    /**
     * Set the object manager
     */
    public function setObjectManager(ObjectManager $objectManager): void
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Get the object manager
     */
    public function getObjectManager(): ObjectManager
    {
        if (! $this->objectManager) {
            throw new RuntimeException('No object manager was set');
        }

        return $this->objectManager;
    }

    /**
     * Set the FQCN of the target object
     */
    public function setTargetClass(string $targetClass): Proxy
    {
        $this->targetClass = $targetClass;

        return $this;
    }

    /**
     * Get the target class
     *
     * @return class-string
     */
    public function getTargetClass(): string
    {
        if (! $this->targetClass) {
            throw new RuntimeException('No target class was set');
        }

        return $this->targetClass;
    }

    /**
     * Set the property to use as the label in the options
     */
    public function setProperty(string $property): Proxy
    {
        $this->property = $property;

        return $this;
    }

    public function getProperty(): mixed
    {
        return $this->property;
    }

    /**
     * Set the label generator callable that is responsible for generating labels for the items in the collection
     *
     * @param callable $callable A callable used to create a label based off of an Entity
     */
    public function setLabelGenerator(callable $callable): void
    {
        $this->labelGenerator = $callable;
    }

    public function getLabelGenerator(): callable|null
    {
        return $this->labelGenerator;
    }

    public function getOptgroupIdentifier(): string|null
    {
        return $this->optgroupIdentifier;
    }

    public function setOptgroupIdentifier(string $optgroupIdentifier): void
    {
        $this->optgroupIdentifier = $optgroupIdentifier;
    }

    public function getOptgroupDefault(): string|null
    {
        return $this->optgroupDefault;
    }

    public function setOptgroupDefault(string $optgroupDefault): void
    {
        $this->optgroupDefault = $optgroupDefault;
    }

    /**
     * Set if the property is a method to use as the label in the options
     */
    public function setIsMethod(bool $method): Proxy
    {
        $this->isMethod = $method;

        return $this;
    }

    public function getIsMethod(): bool|null
    {
        return $this->isMethod;
    }

    /** Set the findMethod property to specify the method to use on repository
     *
     * @param mixed[] $findMethod
     */
    public function setFindMethod(array $findMethod): Proxy
    {
        $this->findMethod = $findMethod;

        return $this;
    }

    /**
     * Get findMethod definition
     *
     * @return mixed[]
     */
    public function getFindMethod(): array
    {
        return $this->findMethod;
    }

    protected function generateLabel(mixed $targetEntity): string|null
    {
        if ($this->getLabelGenerator() === null) {
            return null;
        }

        return call_user_func($this->getLabelGenerator(), $targetEntity);
    }

    /** @throws RuntimeException */
    public function getValue(mixed $value): mixed
    {
        $metadata = $this->getObjectManager()->getClassMetadata($this->getTargetClass());

        if (is_object($value)) {
            if ($value instanceof Collection) {
                $data = [];

                foreach ($value as $object) {
                    $values = $metadata->getIdentifierValues($object);
                    $data[] = array_shift($values);
                }

                $value = $data;
            } else {
                $metadata   = $this->getObjectManager()->getClassMetadata($value::class);
                $identifier = $metadata->getIdentifierFieldNames();

                if ($identifier !== null && count($identifier) > 1) {
                    // Handling composite (multiple) identifiers is not yet supported
                    throw new BadMethodCallException(sprintf(
                        'Composite identiers are not yet supported in %s.',
                        self::class,
                    ));
                }

                $value = current($metadata->getIdentifierValues($value));
            }
        }

        return $value;
    }

    /**
     * Load objects
     *
     * @throws RuntimeException
     * @throws Exception\InvalidRepositoryResultException
     */
    protected function loadObjects(): void
    {
        if (! empty($this->objects)) {
            return;
        }

        $findMethod = $this->getFindMethod();

        if (! $findMethod) {
            $findMethodName = 'findAll';
            $repository     = $this->objectManager->getRepository($this->getTargetClass());
            $objects        = $repository->findAll();
        } else {
            if (! isset($findMethod['name'])) {
                throw new RuntimeException('No method name was set');
            }

            $findMethodName   = $findMethod['name'];
            $findMethodParams = isset($findMethod['params']) ? array_change_key_case($findMethod['params']) : [];
            $repository       = $this->objectManager->getRepository($this->getTargetClass());

            if (! method_exists($repository, $findMethodName)) {
                throw new RuntimeException(
                    sprintf(
                        'Method "%s" could not be found in repository "%s"',
                        $findMethodName,
                        $repository::class,
                    ),
                );
            }

            $r    = new ReflectionMethod($repository, $findMethodName);
            $args = [];

            foreach ($r->getParameters() as $param) {
                if (array_key_exists(strtolower($param->getName()), $findMethodParams)) {
                    $args[] = $findMethodParams[strtolower($param->getName())];
                } elseif ($param->isDefaultValueAvailable()) {
                    $args[] = $param->getDefaultValue();
                } elseif (! $param->isOptional()) {
                    throw new RuntimeException(
                        sprintf(
                            'Required parameter "%s" with no default value for method "%s" in repository "%s"'
                            . ' was not provided',
                            $param->getName(),
                            $findMethodName,
                            $repository::class,
                        ),
                    );
                }
            }

            $objects = $r->invokeArgs($repository, $args);
        }

        $this->guardForArrayOrTraversable(
            $objects,
            sprintf('%s::%s() return value', $repository::class, $findMethodName),
            InvalidRepositoryResultException::class,
        );

        $this->objects = $objects;
    }

    /**
     * Load value options
     *
     * @throws RuntimeException
     */
    protected function loadValueOptions(): void
    {
        $metadata         = $this->getObjectManager()->getClassMetadata($this->getTargetClass());
        $identifier       = $metadata->getIdentifierFieldNames();
        $objects          = $this->getObjects();
        $options          = [];
        $optionAttributes = [];

        if ($this->displayEmptyItem) {
            $options[''] = $this->getEmptyItemLabel();
        }

        foreach ($objects as $key => $object) {
            $generatedLabel = $this->generateLabel($object);
            if ($generatedLabel !== null) {
                $label = $generatedLabel;
            } elseif ($this->property !== null) {
                $property = $this->property;
                if (
                    ($this->getIsMethod() === false || $this->getIsMethod() === null)
                    && ! $metadata->hasField($property)
                ) {
                    throw new RuntimeException(
                        sprintf(
                            'Property "%s" could not be found in object "%s"',
                            $property,
                            $this->getTargetClass(),
                        ),
                    );
                }

                $getter = 'get' . $this->inflector->classify($property);

                if (! is_callable([$object, $getter])) {
                    throw new RuntimeException(
                        sprintf('Method "%s::%s" is not callable', $this->targetClass, $getter),
                    );
                }

                $label = $object->{$getter}();
            } else {
                if (! is_callable([$object, '__toString'])) {
                    throw new RuntimeException(
                        sprintf(
                            '%s must have a "__toString()" method defined if you have not set a property'
                            . ' or method to use.',
                            $this->getTargetClass(),
                        ),
                    );
                }

                $label = (string) $object;
            }

            if ($identifier !== null && count($identifier) > 1) {
                $value = $key;
            } else {
                $value = current($metadata->getIdentifierValues($object));
            }

            foreach ($this->getOptionAttributes() as $optionKey => $optionValue) {
                if (is_string($optionValue)) {
                    $optionAttributes[$optionKey] = $optionValue;

                    continue;
                }

                if (is_callable($optionValue)) {
                    $callableValue                = call_user_func($optionValue, $object);
                    $optionAttributes[$optionKey] = (string) $callableValue;

                    continue;
                }

                throw new RuntimeException(
                    sprintf(
                        'Parameter "option_attributes" expects an array of key => value where value is of type'
                        . '"string" or "callable". Value of type "%s" found.',
                        gettype($optionValue),
                    ),
                );
            }

            // If no optgroup_identifier has been configured, apply default handling and continue
            if ($this->getOptgroupIdentifier() === null) {
                $options[] = ['label' => $label, 'value' => $value, 'attributes' => $optionAttributes];

                continue;
            }

            // optgroup_identifier found, handle grouping
            $optgroupGetter = 'get' . $this->inflector->classify($this->getOptgroupIdentifier());

            if (! is_callable([$object, $optgroupGetter])) {
                throw new RuntimeException(
                    sprintf('Method "%s::%s" is not callable', $this->targetClass, $optgroupGetter),
                );
            }

            $optgroup = $object->{$optgroupGetter}();

            // optgroup_identifier contains a valid group-name. Handle default grouping.
            if ($optgroup !== null && trim($optgroup) !== '') {
                $options[$optgroup]['label']     = $optgroup;
                $options[$optgroup]['options'][] = [
                    'label'      => $label,
                    'value'      => $value,
                    'attributes' => $optionAttributes,
                ];

                continue;
            }

            $optgroupDefault = $this->getOptgroupDefault();

            // No optgroup_default has been provided. Line up without a group
            if ($optgroupDefault === null) {
                $options[] = ['label' => $label, 'value' => $value, 'attributes' => $optionAttributes];

                continue;
            }

            // Line up entry with optgroup_default
            $options[$optgroupDefault]['label']     = $optgroupDefault;
            $options[$optgroupDefault]['options'][] = [
                'label'      => $label,
                'value'      => $value,
                'attributes' => $optionAttributes,
            ];
        }

        $this->valueOptions = $options;
    }
}
