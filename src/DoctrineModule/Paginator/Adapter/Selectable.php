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

namespace DoctrineModule\Paginator\Adapter;

use Doctrine\Common\Collections\Selectable as DoctrineSelectable;
use Doctrine\Common\Collections\Criteria as DoctrineCriteria;
use Zend\Paginator\Adapter\AdapterInterface;

/**
 * Provides a wrapper around a Selectable object
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class Selectable implements AdapterInterface
{
    /**
     * @var DoctrineSelectable
     */
    protected $selectable;

    /**
     * @var DoctrineCriteria
     */
    protected $criteria;

    /**
     * @var null|int
     */
    protected $count = null;


    /**
     * Create a paginator around a Selectable object. You can also provide an optional Criteria object with
     * some predefined filters
     *
     * @param \Doctrine\Common\Collections\Selectable    $selectable
     * @param \Doctrine\Common\Collections\Criteria|null $criteria
     */
    public function __construct(DoctrineSelectable $selectable, DoctrineCriteria $criteria = null)
    {
        if (null === $criteria) {
            $criteria = new DoctrineCriteria();
        }

        $this->selectable = $selectable;
        $this->criteria   = clone $criteria;
    }

    /**
     * {@inheritDoc}
     */
    public function getItems($offset, $itemCountPerPage)
    {
        $this->criteria->setFirstResult($offset)
                       ->setMaxResults($itemCountPerPage);

        $collection  = $this->selectable->matching($this->criteria)->toArray();
        $this->count = count($collection);

        return $collection;
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        return $this->count;
    }
}
