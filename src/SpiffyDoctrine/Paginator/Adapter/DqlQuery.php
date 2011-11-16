<?php

    namespace SpiffyDoctrine\Paginator\Adapter;

    use Doctrine\ORM\EntityManager,
        Doctrine\ORM\Query,
        Doctrine\ORM\QueryBuilder,
        Zend\Paginator\Adapter;


    class DqlQuery implements Adapter
    {
        /**
         * Doctrine EntityManager
         *
         * @var EntityManager
         */
        protected $em;

        /**
         * Query
         *
         * @var Query
         */
        protected $query;

        /**
         *
         * @var Query
         */
        protected $countQuery;

        /**
         *
         * @var integer
         */
        protected $count;


        /**
         * Constructor
         *
         * Required options are:
         *     - em                EntityManager to use
         *     - query             Query (it can either be a pure DQL string, a Query object or a QueryBuilder object)
         *
         * Optional are:
         *     - hydration_mode    Specify the hydration mode when the query is fetched
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
            }
            elseif ($options['query'] instanceof Query) {
                $this->query = $options['query'];
            }
            elseif ($options['query'] instanceof QueryBuilder) {
                $this->query = $options['query']->getDQL();
            }
            else {
                throw new Exception\InvalidArgumentException('Query must either be a DQL string, a Query object or a QueryBuilder object.');
            }

            if (array_key_exists('hydration_mode', $options)) {
                $this->query->setHydrationMode($options['hydration_mode']);
            }
        }

        /**
         * Returns an collection of items for a page. Please notice that the first offset is assumed to be 1 (not 0)
         *
         * @param  integer $offset Page offset
         * @param  integer $itemCountPerPage Number of items per page
         * @return array
         */
        public function getItems($offset, $itemCountPerPage)
        {
            $this->query->setFirstResult(($offset - 1) * $itemCountPerPage)
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
         *
         * @return integer
         */
        protected function getEntityCount()
        {
            if ($this->countQuery === null) {
                $this->countQuery = clone ($this->query);
                $this->countQuery->setHint(Query::HINT_CUSTOM_TREE_WALKERS, array('DoctrineExtensions\Paginate\CountWalker'));
            }

            return $this->countQuery->getSingleScalarResult();
        }
    }