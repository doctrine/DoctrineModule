# Extra goodies included with DoctrineModule
The items listed below are entirely optional and are intended to enhance integration between Zend Framework 2 and
Doctrine 2 .

## EntityExists and NoEntityExists Validators
The EntityExists and NoEntityExists are validators similar to Zend\Validator\Db validators. You can
pass a variety of options to determine validity. The most basic use case requires an entity manager (em),
an entity, and a field. You also have the option of specifying a query_builder Closure to use if you
want to fine tune the results.

```php
<?php
$validator = new DoctrineModule\Validator\NoEntityExists(array(
   'em' => $this->getLocator()->get('Doctrine\ORM\EntityManager'),
   'entity' => 'My\Entity\User',
   'field' => 'username',
   'query_builder' => function($er) {
       return $er->createQueryBuilder('q');
   }
));
echo $validator->isValid('test') ? 'Valid' : 'Invalid! Duplicate found!';
```

## Authentication adapter for Zend\Authentication
The authentication adapter is intended to provide an adapter for `Zend\Authentication`. It works much
like the `DbTable` adapter in the core framework. You must provide the entity manager instance,
entity name, identity field, and credential field. You can optionally provide a callable method
to perform hashing on the password prior to checking for validation.

```php
<?php
$adapter = new \DoctrineModule\Authentication\Adapter\DoctrineObject(
    $this->getLocator()->get('Doctrine\ORM\EntityManager'),
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
$adapter->setIdentityValue('username');
$adapter->setCredentialValue('password');
$result = $adapter->authenticate();

echo $result->isValid() ? 'Authenticated!' : 'Could not authenticate';
```

## Custom DBAL Types
To register custom Doctrine DBAL types, simply add them to the `doctrine.configuration.my_dbal_default.types`
key in you configuration file:

```php
<?php
return array(
    'doctrine' => array(
        'configuration' => array(
            'my_dbal_default' => array(
                'types' => array(
                    // You can override a default type
                    'date' => 'My\DBAL\Types\DateType',
                    // And set new ones
                    'tinyint' => 'My\DBAL\Types\TinyIntType',
                ),
            ),
        ),
    ),
);
```

You are now able to use them, for example, in your ORM entities:

```php
<?php

class User
{
    /**
     * @ORM\Column(type="date")
     */
    protected $birthdate;

    /**
     * @ORM\Column(type="tinyint")
     */
    protected $houses;
}
```
