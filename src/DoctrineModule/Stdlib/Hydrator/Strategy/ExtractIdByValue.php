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

use Doctrine\Common\Collections\Collection;

/**
 * Implements a strategy that only extracts the ID fields of entities
 * in collections.
 * 
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Liam O'Boyle <liam@ontheroad.net.nz>
 */
class ExtractIdByValue extends AbstractExtractObjectStrategy
{
    /**
     * {@inheritdoc}
     */
    protected function getIdentifier($object, $metadata)
    {
        return $this->getIdentifierByValue($object, $metadata);
    }

    /**
     * Handle a single object.
     */
    protected function extractObject($object)
    {
        $metadata = $this->getMetadata($object);

        return $this->getIdentifier($object, $metadata);
    }

    /**
     * Handle a set of entities.
     */
    protected function extractCollection(Collection $collection)
    {
        if ($collection->isEmpty()) {
            return [];
        }

        $results  = [];
        $object   = $collection->first();
        $metadata = $this->getMetadata($object);

        foreach ($collection as $object) {
            $results[] = $this->getIdentifier($object, $metadata);
        }

        return $results;
    }
}
