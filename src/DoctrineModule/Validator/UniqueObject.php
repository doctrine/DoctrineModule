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
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Zend\Validator\Exception;

/**
 * Class that validates if objects exist in a given repository with a given list of matched fields only once.
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Oskar Bley <oskar@programming-php.net>
 */
class UniqueObject extends ObjectExists implements ObjectManagerAwareInterface
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
     * @return ObjectManager
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $objectManager
     */
    public function setObjectManager(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
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
        return $this->objectManager
                    ->getClassMetadata($this->objectRepository->getClassName())
                    ->getIdentifierValues($match);
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
        foreach ($this->getIdentifiers() as $identifierField) {

            if (!isset($context[$identifierField])) {
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
