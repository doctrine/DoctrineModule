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

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;

/**
 * Strategy capable of hydrating the value of a `to-one` association field
 * into an array of associated items
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @since   0.8.0
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class ToOneAssociationStrategy implements StrategyInterface
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var ClassMetadata
     */
    protected $metadata;

    /**
     * @var string
     */
    protected $associationName;

    public function __construct(ObjectManager $objectManager, ClassMetadata $metadata, $associationName)
    {
        $this->objectManager   = $objectManager;
        $this->metadata        = $metadata;
        $this->associationName = (string) $associationName;
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
     * @todo handle null or partially null identifiers (with an utility?)
     */
    public function hydrate($value)
    {
        $targetClass    = $this->metadata->getAssociationTargetClass($this->associationName);
        $targetMetadata = $this->objectManager->getClassMetadata($targetClass);

        if (null === $value || $value instanceof $targetClass) {
            return $value;
        }

        if (is_array($value) && array_keys($value) != $targetMetadata->getIdentifier()) {
            // $value is most likely an array of fieldset data
            $identifiers = array_intersect_key($value, array_flip($targetMetadata->getIdentifier()));
            $object      = $this->objectManager->find($targetClass, $identifiers);

            return $object ?: $targetMetadata->getReflectionClass()->newInstance();
        }

        // @todo should use $targetMetadata->getName()
        return $this->objectManager->find($targetClass, $value);
    }
}
