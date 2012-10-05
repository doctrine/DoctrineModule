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

namespace DoctrineModule\Util;

use Doctrine\Common\Collections\Collection;


/**
 * This class provides some useful util functions when dealing with Doctrine Collections.
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @since   0.5.0
 * @author  Michael Gallego <mic.gallego@gmail.com>
 */
class CollectionUtils
{
    /**
     * This function performs a kind of "intersection union" operation, and is useful especially when dealing
     * with dynamic forms. For instance, if a collection contains existing elements and a form remove one of those
     * elements, this function will return a Collection that contains all the elements from $collection1, minus ones
     * that are not present in $collection2. This is used internally in the DoctrineModule hydrator, so that the
     * work is done for you automatically
     *
     * @param  Collection $collection1
     * @param  Collection $collection2
     * @return Collection
     */
    public static function intersectUnion(Collection $collection1, Collection $collection2)
    {
        // Don't make the work both
        if ($collection1 === $collection2) {
            return $collection1;
        }

        $toRemove = array();

        foreach ($collection1 as $key1 => $value1) {
            $elementFound = false;

            foreach ($collection2 as $key2 => $value2) {
                if ($value1 === $value2) {
                    $elementFound = true;
                    unset ($collection2[$key2]);

                    break;
                }
            }

            if (!$elementFound) {
                $toRemove[] = $key1;
            }
        }

        // Remove elements that are in $collection1 but not in $collection2
        foreach ($toRemove as $key) {
            $collection1->remove($key);
        }

        // Add elements that are in $collection2 but not in $collection1
        foreach ($collection2 as $value) {
            $collection1->add($value);
        }

        return $collection1;
    }
}

