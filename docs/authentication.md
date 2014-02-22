# Authentication

Authentication through Doctrine is fully supported by DoctrineModule through an authentication adapter, and a specific storage implementation that relies on the database. Most of the time, those classes will be used in conjunction with `Zend\Authentication\AuthenticationService` class.

### Simple example

In order to authenticate a user (or anything else) against Doctrine, the following workflow will be use :

1. Create an authentication adapter that contains options about the entity that is authenticated (credential property, identity property…).
2. Create a storage adapter. If the authentication succeeds, the identifier of the entity will be automatically stored in session.
3. Create a `Zend\Authentication\AuthenticationService`instance that contains both the authentication adapter and the storage adapter.

#### Authentication factory

To make your life easier, DoctrineModule provides an Authentication factory through the ``DoctrineModule\Options\Authentication`` class.

The first task is to configure the Authentication by adding the ``authentication`` key to the ``doctrine`` key in your config file (we assume here that the entity we want to authentication is simply called `Application\Entity\User`):

```php
// in your module.config.php:

return array(
    'doctrine' => array(
        'authentication' => array(
            'orm_default' => array(
                'object_manager' => 'Doctrine\ORM\EntityManager',
                'identity_class' => 'Application\Entity\User',
                'identity_property' => 'email',
                'credential_property' => 'password',
            ),
        ),
    ),
);
```

Here are some explanations about the keys:

* the `object_manager` key can either be a concrete instance of a `Doctrine\Common\Persistence\ObjectManager` or a single string that will fetched from the Service Manager in order to get a concrete instance. If you are using DoctrineORMModule, you can simply write 'Doctrine\ORM\EntityManager' (as the EntityManager implements the class `Doctrine\Common\Persistence\ObjectManager`).
* the `identity_class` contains the FQCN of the entity that will be used during the authentication process.
* the `identity_property` contains the name of the property that will be used as the identity property (most often, this is email, username…). Please note that we are talking here of the PROPERTY, not the table column name (although it can be the same in most of the cases).
* the `credential_property` contains the name of the property that will be used as the credential property (most often, this is password…).

The authentication accept some more options that can be used :

* the `object_repository` can be used instead of the `object_manager` key. Most of the time you won't deal with the one, as specifying the `identity_class` name will automatically fetch the `object_repository` for you.
* the `credential_callable` is a very useful option that allow you to perform some custom logic when checking if the credential is correct. For instance, if your password are encrypted using Bcrypt algorithm, you will need to perform specific logic. This option can be any callable function (closure, class method…). This function will be given the complete entity fetched from the database, and the credential that was given by the user during the authentication process.

Here is an example code that adds the `credential_callable` function to our previous example :

```php
// in your module.config.php:

return array(
    'doctrine' => array(
        'authentication' => array(
            'orm_default' => array(
                'object_manager' => 'Doctrine\ORM\EntityManager',
                'identity_class' => 'Application\Entity\User',
                'identity_property' => 'email',
                'credential_property' => 'password',
                'credential_callable' => function(User $user, $passwordGiven) {
                    return my_awesome_check_test($user->getPassword(), $passwordGiven);
                },
            ),
        ),
    ),
);
```

#### Creating the AuthenticationService

Now that we have configured the authentication, we need still need to tell Zend Framework how to construct a correct ``Zend\Authentication\AuthenticationService`` instance. For this, add the following code in your Module.php class:

```php
namespace Application;

use Zend\Authentication\AuthenticationService;

class Module
{
    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'Zend\Authentication\AuthenticationService' => function($serviceManager) {
                    // If you are using DoctrineORMModule:
                    return $serviceManager->get('doctrine.authenticationservice.orm_default');

                    // If you are using DoctrineODMModule:
                    return $serviceManager->get('doctrine.authenticationservice.odm_default');
                }
            )
        );
    }
}
```

Please note that Iam using here a ``Zend\Authentication\AuthenticationService`` name, but it can be anything else (``my_auth_service``…). However, using the name ``Zend\Authentication\AuthenticationService`` will allow it to be recognised by the ZF2 view helper.

#### Using the AuthenticationService

Now that we have defined how to create a `Zend\Authentication\AuthenticationService` object, we can use it in our code. For more information about Zend authentication mechanisms, please read [the ZF 2 Authentication's documentation](http://framework.zend.com/manual/2.0/en/modules/zend.authentication.intro.html).

Here is an example of you we could use it from a controller action (we stripped any Form things for simplicity):

```php
public function loginAction()
{
    $data = $this->getRequest()->getPost();

    // If you used another name for the authentication service, change it here
    $authService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');

    $adapter = $authService->getAdapter();
    $adapter->setIdentityValue($data['login']);
    $adapter->setCredentialValue($data['password']);
    $authResult = $authService->authenticate();

    if ($authResult->isValid()) {
        return $this->redirect()->toRoute('home');
    }

    return new ViewModel(array(
        'error' => 'Your authentication credentials are not valid',
    ));
}
```

Of course, doing this in the controller is not the best practice, and you'd better move that kind of logic to a service layer. But this is how it works.

Note that when the authentication is valid, we first get the identity :

```php
$identity = $authenticationResult->getIdentity();
```

This will return the full entity (in our case, an `Application\Entity\User` instance). However, storing a full entity in session is not a recommended practice. That's why, when writing the identity :

```php
$authService->getStorage()->write($identity);
```

The storage automatically extracts ONLY the identifier values and only store this in session (this avoid to store in session a serialized entity, which is a bad practice). Later, when you want to retrieve the logged user :

```php
$authenticationService = $this->serviceLocator()->get('Zend\Authentication\AuthenticationService');
$loggedUser = $authenticationService->getIdentity();
```

The authentication storage will automatically handle the conversion from saved data to managed entity and the opposite. It will avoid serializing entities since that is a strongly discouraged practice.

#### View helper and controller helper

You may also need to know if there is an authenticated user within your other controllers or in views. ZF2 provides a controller plugin and a view helper you may use.

Here is how you use it in your controller :

```php
public function testAction()
{
    if ($user = $this->identity()) {
        // someone is logged !
    } else {
        // not logged in
    }
}
```

And in your view :

```php
<?php
    if ($user = $this->identity()) {
        echo 'Logged in as ' . $this->escapeHtml($user->getUsername());
    } else {
        echo 'Not logged in';
    }
?>
```
