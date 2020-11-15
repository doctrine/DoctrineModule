<?php

declare(strict_types=1);

namespace DoctrineModule\Validator;

use Doctrine\Persistence\ObjectManager;
use Laminas\Validator\Exception;

use function array_diff_assoc;
use function array_key_exists;
use function count;
use function get_class;
use function gettype;
use function is_object;
use function sprintf;

/**
 * Class that validates if objects exist in a given repository with a given list of matched fields only once.
 */
class UniqueObject extends ObjectExists
{
    /**
     * Error constants
     */
    public const ERROR_OBJECT_NOT_UNIQUE = 'objectNotUnique';

    // phpcs:disable Generic.Files.LineLength
    /** @var mixed[] */
    protected $messageTemplates = [self::ERROR_OBJECT_NOT_UNIQUE => "There is already another object matching '%value%'"];
    // phpcs:enable Generic.Files.LineLength

    /** @var ObjectManager */
    protected $objectManager;

    /** @var bool */
    protected $useContext;

    /***
     * Constructor
     *
     * @param mixed[] $options required keys are `object_repository`, which must be an instance of
     *                       Doctrine\Persistence\ObjectRepository, `object_manager`, which
     *                       must be an instance of Doctrine\Persistence\ObjectManager,
     *                       and `fields`, with either a string or an array of strings representing
     *                       the fields to be matched by the validator.
     *
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(array $options)
    {
        parent::__construct($options);

        if (! isset($options['object_manager']) || ! $options['object_manager'] instanceof ObjectManager) {
            if (! array_key_exists('object_manager', $options)) {
                $provided = 'nothing';
            } else {
                if (is_object($options['object_manager'])) {
                    $provided = get_class($options['object_manager']);
                } else {
                    $provided = gettype($options['object_manager']);
                }
            }

            throw new Exception\InvalidArgumentException(
                sprintf(
                    'Option "object_manager" is required and must be an instance of'
                    . ' Doctrine\Persistence\ObjectManager, %s given',
                    $provided
                )
            );
        }

        $this->objectManager = $options['object_manager'];
        $this->useContext    = isset($options['use_context']) ? (bool) $options['use_context'] : false;
    }

    /**
     * Returns false if there is another object with the same field values but other identifiers.
     *
     * @param mixed $value
     * @param mixed $context
     */
    public function isValid($value, $context = null): bool
    {
        if (! $this->useContext) {
            $context = (array) $value;
        }

        $cleanedValue = $this->cleanSearchValue($value);
        $match        = $this->objectRepository->findOneBy($cleanedValue);

        if (! is_object($match)) {
            return true;
        }

        $expectedIdentifiers = $this->getExpectedIdentifiers($context);
        $foundIdentifiers    = $this->getFoundIdentifiers($match);

        if (count(array_diff_assoc($expectedIdentifiers, $foundIdentifiers)) === 0) {
            return true;
        }

        $this->error(self::ERROR_OBJECT_NOT_UNIQUE, $value);

        return false;
    }

    /**
     * Gets the identifiers from the matched object.
     *
     * @return mixed[]
     *
     * @throws Exception\RuntimeException
     */
    protected function getFoundIdentifiers(object $match): array
    {
        return $this->objectManager
                    ->getClassMetadata($this->objectRepository->getClassName())
                    ->getIdentifierValues($match);
    }

    /**
     * Gets the identifiers from the context.
     *
     * @param  mixed[]|object $context
     *
     * @return mixed[]
     *
     * @throws Exception\RuntimeException
     */
    protected function getExpectedIdentifiers($context = null): array
    {
        if ($context === null) {
            throw new Exception\RuntimeException(
                'Expected context to be an array but is null'
            );
        }

        $className = $this->objectRepository->getClassName();

        if ($context instanceof $className) {
            return $this->objectManager
                        ->getClassMetadata($this->objectRepository->getClassName())
                        ->getIdentifierValues($context);
        }

        $result = [];
        foreach ($this->getIdentifiers() as $identifierField) {
            if (! array_key_exists($identifierField, $context)) {
                throw new Exception\RuntimeException(sprintf('Expected context to contain %s', $identifierField));
            }

            $result[$identifierField] = $context[$identifierField];
        }

        return $result;
    }

    /**
     * @return mixed[] the names of the identifiers
     */
    protected function getIdentifiers(): array
    {
        return $this->objectManager
                    ->getClassMetadata($this->objectRepository->getClassName())
                    ->getIdentifierFieldNames();
    }
}
