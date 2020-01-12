<?php

namespace DoctrineModule\Validator;

use Laminas\Validator\AbstractValidator;
use Laminas\Validator\Exception;
use Doctrine\Common\Persistence\ObjectRepository;
use Laminas\Stdlib\ArrayUtils;

/**
 * Class that validates if objects exist in a given repository with a given list of matched fields
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @since   0.4.0
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class ObjectExists extends AbstractValidator
{
    /**
     * Error constants
     */
    const ERROR_NO_OBJECT_FOUND = 'noObjectFound';

    /**
     * @var array Message templates
     */
    protected $messageTemplates = [
        self::ERROR_NO_OBJECT_FOUND => "No object matching '%value%' was found",
    ];

    /**
     * ObjectRepository from which to search for entities
     *
     * @var ObjectRepository
     */
    protected $objectRepository;

    /**
     * Fields to be checked
     *
     * @var array
     */
    protected $fields;

    /**
     * Constructor
     *
     * @param array $options required keys are `object_repository`, which must be an instance of
     *                       Doctrine\Common\Persistence\ObjectRepository, and `fields`, with either
     *                       a string or an array of strings representing the fields to be matched by the validator.
     * @throws \Laminas\Validator\Exception\InvalidArgumentException
     */
    public function __construct(array $options)
    {
        if (! isset($options['object_repository']) || ! $options['object_repository'] instanceof ObjectRepository) {
            if (! array_key_exists('object_repository', $options)) {
                $provided = 'nothing';
            } else {
                if (is_object($options['object_repository'])) {
                    $provided = get_class($options['object_repository']);
                } else {
                    $provided = getType($options['object_repository']);
                }
            }

            throw new Exception\InvalidArgumentException(
                sprintf(
                    'Option "object_repository" is required and must be an instance of'
                    . ' Doctrine\Common\Persistence\ObjectRepository, %s given',
                    $provided
                )
            );
        }

        $this->objectRepository = $options['object_repository'];

        if (! isset($options['fields'])) {
            throw new Exception\InvalidArgumentException(
                'Key `fields` must be provided and be a field or a list of fields to be used when searching for'
                . ' existing instances'
            );
        }

        $this->fields = $options['fields'];
        $this->validateFields();

        parent::__construct($options);
    }

    /**
     * Filters and validates the fields passed to the constructor
     *
     * @throws \Laminas\Validator\Exception\InvalidArgumentException
     * @return array
     */
    private function validateFields()
    {
        $fields = (array) $this->fields;

        if (empty($fields)) {
            throw new Exception\InvalidArgumentException('Provided fields list was empty!');
        }

        foreach ($fields as $key => $field) {
            if (! is_string($field)) {
                throw new Exception\InvalidArgumentException(
                    sprintf('Provided fields must be strings, %s provided for key %s', gettype($field), $key)
                );
            }
        }

        $this->fields = array_values($fields);
    }

    /**
     * @param string|array $value a field value or an array of field values if more fields have been configured to be
     *                      matched
     * @return array
     * @throws \Laminas\Validator\Exception\RuntimeException
     */
    protected function cleanSearchValue($value)
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
                            $field
                        )
                    );
                }

                $matchedFieldsValues[$field] = $value[$field];
            }
        } else {
            $matchedFieldsValues = @array_combine($this->fields, $value);

            if (false === $matchedFieldsValues) {
                throw new Exception\RuntimeException(
                    sprintf(
                        'Provided values count is %s, while expected number of fields to be matched is %s',
                        count($value),
                        count($this->fields)
                    )
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
