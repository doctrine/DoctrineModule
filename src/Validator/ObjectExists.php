<?php

declare(strict_types=1);

namespace DoctrineModule\Validator;

use Doctrine\Persistence\ObjectRepository;
use Laminas\Stdlib\ArrayUtils;
use Laminas\Validator\AbstractValidator;
use Laminas\Validator\Exception;
use ValueError;

use function array_combine;
use function array_key_exists;
use function array_values;
use function count;
use function gettype;
use function is_countable;
use function is_object;
use function is_string;
use function sprintf;

/**
 * Class that validates if objects exist in a given repository with a given list of matched fields
 */
class ObjectExists extends AbstractValidator
{
    /**
     * Error constants
     */
    public const ERROR_NO_OBJECT_FOUND = 'noObjectFound';

    /** @var mixed[] Message templates */
    protected array $messageTemplates = [self::ERROR_NO_OBJECT_FOUND => "No object matching '%value%' was found"];

    /**
     * ObjectRepository from which to search for entities
     */
    protected ObjectRepository $objectRepository;

    /**
     * Fields to be checked
     */
    protected mixed $fields = null;

    /**
     * Constructor
     *
     * @param mixed[] $options required keys are `object_repository`, which must be an instance of
     *                       Doctrine\Persistence\ObjectRepository, and `fields`, with either
     *                       a string or an array of strings representing the fields to be matched by the validator.
     *
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(array $options)
    {
        if (! isset($options['object_repository']) || ! $options['object_repository'] instanceof ObjectRepository) {
            if (! array_key_exists('object_repository', $options)) {
                $provided = 'nothing';
            } else {
                if (is_object($options['object_repository'])) {
                    $provided = $options['object_repository']::class;
                } else {
                    $provided = gettype($options['object_repository']);
                }
            }

            throw new Exception\InvalidArgumentException(
                sprintf(
                    'Option "object_repository" is required and must be an instance of'
                    . ' Doctrine\Persistence\ObjectRepository, %s given',
                    $provided,
                ),
            );
        }

        $this->objectRepository = $options['object_repository'];

        if (! isset($options['fields'])) {
            throw new Exception\InvalidArgumentException(
                'Key `fields` must be provided and be a field or a list of fields to be used when searching for'
                . ' existing instances',
            );
        }

        $this->fields = $options['fields'];
        $this->validateFields();

        parent::__construct($options);
    }

    /**
     * Filters and validates the fields passed to the constructor
     *
     * @return mixed[]
     *
     * @throws Exception\InvalidArgumentException
     */
    private function validateFields(): array
    {
        $fields = (array) $this->fields;

        if (empty($fields)) {
            throw new Exception\InvalidArgumentException('Provided fields list was empty!');
        }

        foreach ($fields as $key => $field) {
            if (! is_string($field)) {
                throw new Exception\InvalidArgumentException(
                    sprintf('Provided fields must be strings, %s provided for key %s', gettype($field), $key),
                );
            }
        }

        $this->fields = array_values($fields);

        return $this->fields;
    }

    /**
     * @param string|int|object|mixed[] $value a field value or an array of field values if more fields have been configured to be
     *                      matched
     *
     * @return mixed[]
     *
     * @throws Exception\RuntimeException
     */
    protected function cleanSearchValue(string|int|object|array $value): array
    {
        $value = is_object($value) ? [$value] : (array) $value;

        if (ArrayUtils::isHashTable($value)) {
            $matchedFieldsValues = [];

            foreach ($this->fields as $field) {
                if (! array_key_exists($field, $value)) {
                    throw new Exception\RuntimeException(
                        sprintf(
                            'Field "%s" was not provided, but was expected since the configured field lists needs'
                            . ' it for validation',
                            $field,
                        ),
                    );
                }

                $matchedFieldsValues[$field] = $value[$field];
            }
        } else {
            try {
                $matchedFieldsValues = @array_combine($this->fields, $value);
            } catch (ValueError) {
                $matchedFieldsValues = false;
            }

            if ($matchedFieldsValues === false) {
                throw new Exception\RuntimeException(
                    sprintf(
                        'Provided values count is %s, while expected number of fields to be matched is %s',
                        count($value),
                        is_countable($this->fields) ? count($this->fields) : 0,
                    ),
                );
            }
        }

        return $matchedFieldsValues;
    }

    /**
     * {@inheritDoc}
     */
    public function isValid($value)
    {
        $cleanedValue = $this->cleanSearchValue($value);
        $match        = $this->objectRepository->findOneBy($cleanedValue);

        if (is_object($match)) {
            return true;
        }

        $this->error(self::ERROR_NO_OBJECT_FOUND, $value);

        return false;
    }
}
