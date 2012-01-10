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
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace DoctrineModule\Paginator\Adapter;

use Doctrine\ORM\EntityManager,
    Doctrine\ORM\Query,
    Doctrine\ORM\QueryBuilder,
    Zend\Paginator\Adapter;

class DqlQuery implements Adapter
{
    /**
     * Doctrine EntityManager
     * @var EntityManager
     */
    protected $em;

    /**
     * Query
     * @var Query
     */
    protected $query;

    /**
     * @var Query
     */
    protected $countQuery;

    /**
     * @var integer
     */
    protected $count;

    /**
     * Constructor
     *
     * Required options are:
     *     - em     EntityManager to use
     *     - query  Query (it can either be a pure DQL string, a Query object or a 
     *              QueryBuilder object)
     *
     * Optional are:
     *     - hydration_mode Specify the hydration mode when the query is fetched
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        if (!array_key_exists('em', $options)) {
            throw new Exception\InvalidArgumentException('No EntityManager was specified.');
        }

        if (!$options['em'] instanceof EntityManager) {
            throw new Exception\InvalidArgumentException('Invalid EntityManager specified.');
        }

        $this->em = $options['em'];

        if (!array_key_exists('query', $options)) {
            throw new Exception\InvalidArgumentException('No query was specified.');
        }

        if (is_string($options['query'])) {
            $this->query = $this->em->createQuery($options['query']);
        } elseif ($options['query'] instanceof Query) {
            $this->query = $options['query'];
        } elseif ($options['query'] instanceof QueryBuilder) {
            $this->query = $options['query']->getQuery();
        } else {
            throw new Exception\InvalidArgumentException(
                'Query must either be a DQL string, a Query object or a QueryBuilder object.'
            );
        }

        if (array_key_exists('hydration_mode', $options)) {
            $this->query->setHydrationMode($options['hydration_mode']);
        }
    }

    /**
     * Returns an collection of items for a page. Please notice that the first offset is 
     * assumed to be 1 (not 0)
     *
     * @param  integer $offset Page offset
     * @param  integer $itemCountPerPage Number of items per page
     * @return array
     */
    public function getItems($offset, $itemCountPerPage)
    {
        $this->query->setFirstResult($offset)
                    ->setMaxResults($itemCountPerPage);

        return $this->query->getResult();
    }

    /**
     * Returns the total number of rows in the result set.
     *
     * @return integer
     */
    public function count()
    {
        if ($this->count === null) {
            $this->count = $this->getEntityCount();
        }

        return $this->count;
    }

    /**
     * @return integer
     */
    protected function getEntityCount()
    {
        if ($this->countQuery === null) {
            $this->countQuery = clone ($this->query);
            $this->countQuery->setParameters($this->query->getParameters(),
            	$this->query->getParameterTypes());
            $this->countQuery->setHint(
                Query::HINT_CUSTOM_TREE_WALKERS, 
                array('DoctrineModule\Paginator\TreeWalker\CountSqlWalker')
            );
        }

        return $this->countQuery->getSingleScalarResult();
    }
}