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
 * and is licensed under the MIT license.
 */

namespace DoctrineModule\Hydrator;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use DoctrineModule\Hydrator\Strategy\CollectionAssociationIdentifierExtractor;
use DoctrineModule\Hydrator\Strategy\SingleAssociationIdentifierExtractor;
use Zfr\Hydrator\AbstractExtractor;
use Zfr\Hydrator\Protection\CircularExtractionTrait;

class DoctrineObjectExtractor extends AbstractExtractor
{
    use CircularExtractionTrait;

    /**
     * @var ClassMetadataFactory
     */
    protected $classMetadataFactory;

    /**
     * @param ClassMetadataFactory $classMetadataFactory
     */
    public function __construct(ClassMetadataFactory $classMetadataFactory)
    {
        $this->classMetadataFactory = $classMetadataFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function extract($object)
    {
        $this->beginExtraction($object);

        $classMetadata = $this->classMetadataFactory->getMetadataFor(get_class($object));

        if ($this->isCircularExtraction($object)) {
            return $this->extractIdentifierValue($object, $classMetadata);
        }

        $data = array_merge(
            $this->extractFields($object, $classMetadata),
            $this->extractAssociations($object, $classMetadata)
        );

        $this->endExtraction($object);

        return $data;
    }

    /**
     * Extract identifier (for now, does not support composite identifiers)
     *
     * @param  object        $object
     * @param  ClassMetadata $classMetadata
     * @return mixed
     */
    protected function extractIdentifierValue($object, ClassMetadata $classMetadata)
    {
        $identifiers = $classMetadata->getIdentifierValues($object);
        return reset($identifiers);
    }

    /**
     * Extract all the fields
     *
     * @param  object        $object
     * @param  ClassMetadata $classMetadata
     * @return array
     */
    protected function extractFields($object, ClassMetadata $classMetadata)
    {
        $data       = [];
        $reflClass  = $classMetadata->getReflectionClass();
        $fieldNames = $this->compositeFilter->filter($classMetadata->getFieldNames());

        foreach ($fieldNames as $fieldName) {
            $reflProperty = $reflClass->getProperty($fieldName);
            $reflProperty->setAccessible(true);

            $data[$fieldName] = $reflProperty->getValue($object);
        }

        return $data;
    }

    /**
     * Extract all the associations
     *
     * @param  object        $object
     * @param  ClassMetadata $classMetadata
     * @return array
     */
    protected function extractAssociations($object, ClassMetadata $classMetadata)
    {
        $data             = [];
        $reflClass        = $classMetadata->getReflectionClass();
        $associationNames = $this->compositeFilter->filter($classMetadata->getAssociationNames());

        foreach ($associationNames as $associationName) {
            if (!$this->hasStrategy($associationName)) {
                if ($classMetadata->isSingleValuedAssociation($associationName)) {
                    $this->addStrategy($associationName, new SingleAssociationIdentifierExtractor());
                } else {
                    $this->addStrategy($associationName, new CollectionAssociationIdentifierExtractor());
                }
            }

            $reflProperty = $reflClass->getProperty($associationName);
            $reflProperty->setAccessible(true);

            $associationMetadata = $this->classMetadataFactory->getMetadataFor(
                $classMetadata->getAssociationTargetClass($associationName)
            );

            $data[$associationName] = $this->extractValue(
                $associationName,
                $reflProperty->getValue($object),
                $associationMetadata
            );
        }

        return $data;
    }
}
