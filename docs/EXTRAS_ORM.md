# Extra goodies included with DoctrineModule
The items listed below are entirely optional and are intended to enhance ZF2/D2 integration.

## EntityExists and NoEntityExists Validators
The EntityExists and NoEntityExists are validators similar to Zend\Validator\Db validators. You can
pass a variety of options to determine validity. The most basic use case requires an entity manager (em),
an entity, and a field. You also have the option of specifying a query_builder Closure to use if you
want to fine tune the results.

    $validator = new \DoctrineModule\Validator\NoEntityExists(array(
       'em' => $this->getLocator()->get('doctrine')->getEntityManager(),
       'entity' => 'My\Entity\User',
       'field' => 'username',
       'query_builder' => function($er) {
           return $er->createQueryBuilder('q');
       }
    ));
    var_dump($validator->isValid('test'));

## Authentication adapter for Zend\Authentication
The authentication adapter is intended to provide an adapter for Zend\Authenticator. It works much
like the DbTable adapter in the core framework. You must provide the entity manager instance,
entity name, identity field, and credential field. You can optionally provide a callable method
to perform hashing on the password prior to checking for validation.

    $adapter = new \DoctrineModule\Authentication\Adapter\DoctrineEntity(
        $this->getLocator()->get('doctrine')->getEntityManager(), // entity manager
        'Application\Test\Entity',
        'username', // optional, default shown
        'password'  // optional, default shown,
        function($identity, $credential) { // optional callable
             return \My\Service\User::hashCredential(
                    $credential,
                    $identity->getSalt(),
                    $identity->getAlgorithm()
                );
        }
    );
    $adapter->setIdentity('username');
    $adapter->setCredential('password');
    $result = $adapter->authenticate();

    var_dump($result);

## Pagination adapter for Zend\Paginator\Paginator
The paginator adapter is intended to provide an adapter for Zend\Adapter. You must provide the 
entity manager (em) and a valid query (currently accepted are pure DQL string, Query object or
QueryBuilder object). You can optionally provide a hydration_mode which defines how data is 
fetched (array, object...). Please refer to the Doctrine manual for more information about 
Doctrine's hydration modes.

IMPORTANT: currently, the paginator adapter only work with single identifier (composite primary keys 
or identity through multiple foreign keys are not supported, because of the CountWalker class).

    $paginatorAdapter = new \DoctrineModule\Paginator\Adapter\DqlQuery(array(
        'em'    => $this->getLocator()->get('doctrine_em'),
        'query' => "SELECT u FROM User u WHERE u.city = 'Paris'"
    ));

    $paginator = new \Zend\Paginator\Paginator($paginatorAdapter);
    $paginator->setCurrentPageNumber(1)
              ->setItemCountPerPage(10);