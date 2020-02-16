<?php

declare(strict_types=1);

namespace DoctrineModule\Validator;

use function is_object;

/**
 * Class that validates if objects does not exist in a given repository with a given list of matched fields
 *
 * @link    http://www.doctrine-project.org/
 */
class NoObjectExists extends ObjectExists
{
    /**
     * Error constants
     */
    public const ERROR_OBJECT_FOUND = 'objectFound';

    /** @var mixed[] Message templates */
    protected $messageTemplates = [self::ERROR_OBJECT_FOUND => "An object matching '%value%' was found"];

    /**
     * {@inheritDoc}
     */
    public function isValid($value)
    {
        $cleanedValue = $this->cleanSearchValue($value);
        $match        = $this->objectRepository->findOneBy($cleanedValue);

        if (is_object($match)) {
            $this->error(self::ERROR_OBJECT_FOUND, $value);

            return false;
        }

        return true;
    }
}
