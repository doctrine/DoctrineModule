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

namespace DoctrineModule\Hydrator\Strategy;

use Zfr\Hydrator\Strategy\StrategyInterface;

/**
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
class CollectionAssociationIdentifierExtractor implements StrategyInterface
{
    /**
     * {@inheritDoc}
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadata $context
     */
    public function extract($value, $context = null)
    {
        $result = [];

        foreach ($value as $object) {
            $identifierValues = $context->getIdentifierValues($object);
            $result[]         = reset($identifierValues);
        }

        return $result;
    }

    /**
     * Converts the given value so that it can be hydrated by the hydrator
     *
     * @param  mixed $value The original value
     * @param  mixed $context An optional context (most often, the object itself)
     * @return mixed
     */
    public function hydrate($value, $context = null)
    {
        // TODO: Implement hydrate() method.
    }
}
