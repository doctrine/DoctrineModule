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
     * @var array Identifier fields
     */
    protected $identifierFields;

    /**
     * Constructor
     *
     * @param array $options required keys are `object_repository`, which must be an instance of
     *                       Doctrine\Common\Persistence\ObjectRepository, and `fields`, with either
     *                       a string or an array of strings representing the fields to be matched by the validator.
     *                       There might be also an key `id_fields`, that must be an string or an array of strings
     *                       containing the identifiers of the entity.
     * @throws \Zend\Validator\Exception\InvalidArgumentException
     */
    public function __construct(array $options)
    {
        $this->identifierFields = isset($options['id_fields'])
                                ? (array) $options['id_fields']
                                : array('id');

        if (count($this->identifierFields) == 0) {
            throw new Exception\InvalidArgumentException('There must be at least one identifier');
        }

        parent::__construct($options);
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
        $value = $this->cleanSearchValue($value);
        $match = $this->objectRepository->findOneBy($value);

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
        $result = array();

        foreach ($this->identifierFields as $identifierField) {
            $getter = 'get' . ucfirst($identifierField);

            if (\method_exists($match, $getter)) {
                $result[$identifierField] = $match->$getter();
            } elseif (\property_exists($match, $identifierField)) {
                $result[$identifierField] = $match->{$identifierField};
            } else {
                throw new Exception\RuntimeException(
                    \sprintf(
                        'Property (%s) in (%s) is not accessible. You should implement %s::%s()',
                        $identifierField,
                        \get_class($match),
                        \get_class($match),
                        $getter
                    )
                );
            }
        }
        return $result;
    }

    /**
     * Gets the identifiers from the context.
     *
     * @param array $context
     * @return array
     * @throws Exception\RuntimeException
     */
    protected function getExpectedIdentifiers(array $context = null)
    {
        if ($context === null) {
            throw new Exception\RuntimeException(
                'Expected context to be an array but is null'
            );
        }

        $result = array();
        foreach ($this->identifierFields as $identifierField) {

            if (!isset($context[$identifierField])) {
                throw new Exception\RuntimeException(\sprintf('Expected context to contain %s', $identifierField));
            }

            $result[$identifierField] = $context[$identifierField];
        }
        return $result;
    }
}
