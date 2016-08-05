<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace DoctrineModule\Validator;

use Doctrine\Common\Persistence\ObjectManager;
use Zend\Validator\Exception;

/**
 * Class that validates if objects exist in a given repository with a given list of matched fields only once.
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Oskar Bley <oskar@programming-php.net>
 */
class UniqueObject extends ObjectExists
{
    /**
     * Error constants
     */
    const ERROR_OBJECT_NOT_UNIQUE = 'objectNotUnique';

    /**
     * @var array Message templates
     */
    protected $messageTemplates = array(
        self::ERROR_OBJECT_NOT_UNIQUE => "There is already another object matching '%value%'",
    );

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var boolean
     */
    protected $useContext;

    /***
     * Constructor
     *
     * @param array $options required keys are `object_repository`, which must be an instance of
     *                       Doctrine\Common\Persistence\ObjectRepository, `object_manager`, which
     *                       must be an instance of Doctrine\Common\Persistence\ObjectManager,
     *                       and `fields`, with either a string or an array of strings representing
     *                       the fields to be matched by the validator.
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(array $options)
    {
        parent::__construct($options);

        if (!isset($options['object_manager']) || !$options['object_manager'] instanceof ObjectManager) {
            if (!array_key_exists('object_manager', $options)) {
                $provided = 'nothing';
            } else {
                if (is_object($options['object_manager'])) {
                    $provided = get_class($options['object_manager']);
                } else {
                    $provided = getType($options['object_manager']);
                }
            }

            throw new Exception\InvalidArgumentException(
                sprintf(
                    'Option "object_manager" is required and must be an instance of'
                    . ' Doctrine\Common\Persistence\ObjectManager, %s given',
                    $provided
                )
            );
        }

        $this->objectManager = $options['object_manager'];
        $this->useContext    = isset($options['use_context']) ? (boolean) $options['use_context'] : false;
    }

    /**
     * Returns false if there is another object with the same field values but other identifiers.
     *
     * @param  mixed $value
     * @param  array $context
     * @return boolean
     */
    public function isValid($value, $context = null)
    {
        if (!$this->useContext) {
            $context = (array) $value;
        }

        $cleanedValue = $this->cleanSearchValue($value);
        $match        = $this->objectRepository->findOneBy($cleanedValue);

        if (!is_object($match)) {
            return true;
        }

        $expectedIdentifiers = $this->getExpectedIdentifiers($context);
        $foundIdentifiers    = $this->getFoundIdentifiers($match);

        if (count(array_diff_assoc($expectedIdentifiers, $foundIdentifiers)) == 0) {
            return true;
        }

        $this->error(self::ERROR_OBJECT_NOT_UNIQUE, $value);
        return false;
    }

    /**
     * Gets the identifiers from the matched object.
     *
     * @param object $match
     * @return array
     * @throws Exception\RuntimeException
     */
    protected function getFoundIdentifiers($match)
    {
        return $this->objectManager
                    ->getClassMetadata($this->objectRepository->getClassName())
                    ->getIdentifierValues($match);
    }

    /**
     * Gets the identifiers from the context.
     *
     * @param  array|object $context
     * @return array
     * @throws Exception\RuntimeException
     */
    protected function getExpectedIdentifiers($context = null)
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

        $result = array();
        foreach ($this->getIdentifiers() as $identifierField) {
            if (!array_key_exists($identifierField, $context)) {
                throw new Exception\RuntimeException(\sprintf('Expected context to contain %s', $identifierField));
            }

            $result[$identifierField] = $context[$identifierField];
        }
        return $result;
    }


    /**
     * @return array the names of the identifiers
     */
    protected function getIdentifiers()
    {
        return $this->objectManager
                    ->getClassMetadata($this->objectRepository->getClassName())
                    ->getIdentifierFieldNames();
    }
}
