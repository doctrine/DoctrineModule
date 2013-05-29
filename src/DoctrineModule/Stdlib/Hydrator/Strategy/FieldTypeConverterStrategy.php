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

namespace DoctrineModule\Stdlib\Hydrator\Strategy;

use DateTime;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;

/**
 * Strategy that converts the hydrated value using a doctrine field type
 * string as reference
 *
 * Currently very simple, handles only `date`, `datetime` and `time` field types.
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @since   0.8.0
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class FieldTypeConverterStrategy implements StrategyInterface
{
    /**
     * @var ClassMetadata
     */
    protected $metadata;

    /**
     * @var string
     */
    protected $fieldName;

    public function __construct(ClassMetadata $metadata, $fieldName)
    {
        $this->metadata  = $metadata;
        $this->fieldName = (string) $fieldName;
    }

    /**
     * {@inheritDoc}
     */
    public function extract($value)
    {
        return $value;
    }

    /**
     * {@inheritDoc}
     *
     * Handles doctrine-specific type conversions for particular field values
     *
     * Currently only handles doctrine ORM "datetime", "time" and "date" mappings
     */
    public function hydrate($value)
    {
        switch ($this->metadata->getTypeOfField($this->fieldName)) {
            case 'datetime':
            case 'time':
            case 'date':
                if ('' === $value) {
                    return null;
                }

                if (is_int($value)) {
                    $dateTime = new DateTime();

                    $dateTime->setTimestamp($value);

                    return $dateTime;
                } elseif (is_string($value)) {
                    return new DateTime($value);
                }
                break;
        }

        return $value;
    }
}
