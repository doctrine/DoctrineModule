# Extra goodies included with SpiffyDoctrine
The items listed below are entirely optional and are intended to enhance ZF2/D2 integration.

## EntityExists and NoEntityExists Validators
The EntityExists and NoEntityExists are validators similar to Zend\Validator\Db validators. You can 
pass a variety of options to determine validity. The most basic use case requires an entity manager (em),
an entity, and a field. You also have the option of specifying a query_builder Closure to use if you
want to fine tune the results. 

    $validator = new \SpiffyDoctrine\Validator\NoEntityExists(array(
       'em' => $this->getLocator()->get('doctrine')->getEntityManager(),
       'entity' => 'SpiffyUser\Entity\User',
       'field' => 'username',
       'query_builder' => function($er) {
           return $er->createQueryBuilder('q');
       }
    ));
    var_dump($validator->isValid('test'));        
        
## Authentication adapter for Zend\Authentication
The authentication adapter is intended to provide an adapter for Zend\Authenticator. It works much
like the DbTable adapter in the core framework. You must provide the entity manager instance,
entity name, identity field, and credential field.

    $adapter = new \SpiffyDoctrine\Authentication\Adapter\DoctrineEntity(
        $this->getLocator()->get('doctrine')->getEntityManager(), // entity manager
        'Application\Test\Entity',
        'username', // optional, default shown
        'password'  // optional, default shown
    );
    $adapter->setIdentity('username');
    $adapter->setCredential('password');
    $result = $adapter->authenticate();
    
    var_dump($result);