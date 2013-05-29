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

use InvalidArgumentException;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;

/**
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @since   0.8.0
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class DoctrineFieldStrategy implements StrategyInterface
{
    protected $classMetadata;
    protected $fieldName;

    public function __construct(ClassMetadata $classMetadata, $fieldName)
    {
        $this->classMetadata = $classMetadata;
        $this->fieldName     = (string) $fieldName;

        if (! ($classMetadata->hasField($fieldName) || $classMetadata->hasAssociation($fieldName))) {
            throw new \InvalidArgumentException(sprintf('Provided metadata has no field named "%s"', $fieldName));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function extract($value, $object = null)
    {
        if (null === $object) {
            // we're not extracting data from a doctrine object, skip
            return $value;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function hydrate($value, $data = null)
    {
        if (null === $data) {
            // we're not hydrating data for a doctrine object, skip
            return $value;
        }

        if ($this->classMetadata->getReflectionClass()->isInstance($value)) {
            return $value;
        }
    }
}
