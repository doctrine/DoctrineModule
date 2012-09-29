# DoctrineModule Documentation

DoctrineModule provides a bridge between Zend Framework 2 and Doctrine 2. It gives you access to features that can be used accross Doctrine 2 ORM as well as Doctrine 2 ODM. We also provide bridge for ORM and ODM through [DoctrineORMModule](https://github.com/doctrine/DoctrineORMModule) and [DoctrineMongoODMModule](https://github.com/doctrine/DoctrineMongoODMModule), respectively.

## Authentication

Authentication through Doctrine is fully supported by DoctrineModule through an authentication adapter, and a specific storage implementation that relies on the database. Most of the time, those classes will be used in conjunction with `Zend\Authentication\AuthenticationService` class.

### Simple example

In order to authenticate a user (or anything else) against Doctrine, the following workflow will be use :

1. Create an authentication adapter that contains options about the entity that is authenticated (credential property, identity property…).
2. Create a storage adapter. If the authentication succeeds, the identifier of the entity will be automatically stored in session.
3. Create a `Zend\Authentication\AuthenticationService`instance that contains both the authentication adapter and the storage adapter.

#### Creating the AuthenticationService


Here is a simple code that follow those three steps (we assume here that the entity we want to authentication is simply called `Application\Entity\User`). For simplicity, I have written this code in the Module.php class.

	namespace Application;
	
	use DoctrineModule\Options\AuthenticationAdapter as AuthenticationAdapterOptions;
	use DoctrineModule\Authentication\Adapter\ObjectRepository as AuthenticationAdapter;
	use DoctrineModule\Authentication\Storage\ObjectRepository as AuthenticationStorage;
	use Zend\Authentication\AuthenticationService;
	use Zend\Authentication\Storage\Session as SessionStorage;
	
	class Module
	{
		public function getServiceConfig()
		{
			return array(
				'factories' => array(
					'Zend\Authentication\AuthenticationService' => function($serviceManager) {
						// Create the Auth adapter
						$authOptions = new AuthenticationAdapterOptions(array(
					   		'object_manager' => 'Doctrine\ORM\EntityManager',
							'identity_class' => 'Application\Entity\User',
							'identity_property' => 'email'
							'credential_property' => 'password'
						));
	
						$authAdapter = new AuthenticationAdapter($authOptions);
						
						// Create the storage adapter
						$metadata = $options->getObjectManager()->getMetadataFactory();
						$sessionStorage = new SessionStorage();
        				$storage = new AuthenticationStorage($authOptions->getObjectRepository(), $metadata, $sessionStorage);
        				
        				// Return the fully constructed AuthenticationService
        				return new AuthenticationService($storage, $authAdapter);
					}
				)
			);
		}
		
Some explanations about this code : we first create the AuthenticationAdapter through an AuthenticationAdapterOptions instance. This option object needs some options : 

* the $metadata variable is used internally by the AuthenticationStorage object to extracts the identifier values. You need to create it.
* the `object_manager` key can either be a concrete instance of a `Doctrine\Common\Persistence\ObjectManager` or a single string that will fetched from the Service Manager in order to get a concrete instance. If you are using DoctrineORMModule, you can simply write 'Doctrine\ORM\EntityManager' (as the EntityManager implements the class `Doctrine\Common\Persistence\ObjectManager`).
* the `identity_class` contains the FQCN of the entity that will be used during the authentication process.
* the `identity_property` contains the name of the property that will be used as the identity property (most often, this is email, username…). Please note that we are talking here of the PROPERTY, not the table column name (although it can be the same in most of the cases).
* the `credential_property` contains the name of the property that will be used as the credential property (most often, this is password…).

The AuthenticationAdapterOptions accept some more options that can be used :

* the `object_repository` can be used instead of the `object_manager` key. Most of the time you won't deal with the one, as specifying the `identity_class` name will automatically fetch the `object_repository` for you.
* the `credential_callable` is a very useful option that allow you to perform some custom logic when checking if the credential is correct. For instance, if your password are encrypted using Bcrypt algorithm, you will need to perform specific logic. This option can be any callable function (closure, class method…). This function will be given the complete entity fetched from the database, and the credential that was given by the user during the authentication process.

Here is an example code that adds the `credential_callable` function to our previous example :

	$authOptions = new AuthenticationAdapterOptions(array(
		'object_manager' => 'Doctrine\ORM\EntityManager',
		'identity_class' => 'Application\Entity\User',
		'identity_property' => 'email'
		'credential_property' => 'password',
		'credential_callable' => function(User $user, $passwordGiven) {
			return ($user->getPassword() === crypt($passwordGiven, $user->getPassword()));
		}
	));


#### Using the AuthenticationService

Now that we have defined how to create a `Zend\Authentication\AuthenticationService` object, we can use it in our code. Here is an example of you we could use it from a controller action :

	public function loginAction()
	{
		// Create the form and checks if the values are valid
		$form = new LoginForm();
		
		if ($this->request->isPost()) {
			$form->setData($this->request->getPost());
			
			if ($form->isValid()) {
				// Fetch the authentication service from the Service Manager
				$authService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
				
				// Set the credential and identity value from the form using the AuthAdapter
				$data = $form->getData();
				$authAdapter = $authService->getAdapter();
				$authAdapter->setIdentityValue($data['login'])
							->setCredentialValue($data['password']);
							
				// Check if this is correct
				$authenticationResult = $adapter->authenticate();

        		if ($authenticationResult->isValid()) {
            		$identity = $authenticationResult->getIdentity();
            		$authService->getStorage()->write($identity);
            		
            		// Perform some redirection …
            		return $this->redirect()->toRoute('home');
        		}
        		
        		// If not correct, send an error message to the view
        		$errorMessage = 'The authentication failed';
			}
		}
		
		return array(
			'form' => $form,
			'message' => $errorMessage
		);
	}
	
Of course, doing this in the controller is not the best practice, and you'd better move that kind of logic to a service layer. But this is how it works.

Note that when the authentication is valid, we first get the identity :

	$identity = $authenticationResult->getIdentity();
	
This will return the full entity (in our case, an `Application\Entity\User` instance). However, storing a full entity in session is not a recommended practice. That's why, when writing the identity :

	$authService->getStorage()->write($identity);
	
The storage automatically extracts ONLY the identifier values and only store this in session. Later, when you want to retrieve the logged user :

	$authenticationService = $this->serviceLocator()->get('Zend\Authentication\AuthenticationService');
	$loggedUser = $authenticationService->getIdentity();
	
The storage will automatically make the database call with the entity identifiers, and effectively return you a fully loaded entity.

#### View helper and controller helper

Knowing if a user is logged and get the logged user is a very common practice that is done both in controller and views. That's why, most often, you will create a View Helper and a Controller Plugin to easily get the logged user.

Here is a sample code that shows you the Controller Plugin :

	<?php

	namespace Application\Controller\Plugin;

	use Zend\Authentication\AuthenticationService;
	use Zend\Mvc\Controller\Plugin\AbstractPlugin;
	
	class UserIdentity extends AbstractPlugin
	{
	    /**
	     * @var AuthenticationService
	     */
	    protected $authenticationService;
	
	    /**
	     * Constructor
	     *
	     * @param AuthenticationService $authenticationService
	     */
	    public function __construct(AuthenticationService $authenticationService)
	    {
	        $this->authenticationService = $authenticationService;
	    }
	
	    /**
	     * @return \Application\Entity\User
	     */
	    public function __invoke()
	    {
	        if ($this->authenticationService->hasIdentity()) {
	            return $this->authenticationService->getIdentity();
	        }
	
	        return null;
	    }
	}
	
The View Helper is very similar :

	<?php
	
	namespace Application\View\Helper;
	
	use Zend\Authentication\AuthenticationService;
	use Zend\View\Helper\AbstractHelper;
	
	class UserIdentity extends AbstractHelper
	{
	    /**
	     * @var AuthenticationService
	     */
	    protected $authenticationService;
	
	    /**
	     * Constructor
	     *
	     * @param AuthenticationService $authenticationService
	     */
	    public function __construct(AuthenticationService $authenticationService)
	    {
	        $this->authenticationService = $authenticationService;
	    }
	
	    /**
	     * @return \Application\Entity\User
	     */
	    public function __invoke()
	    {
	        if ($this->authenticationService->hasIdentity()) {
	            return $this->authenticationService->getIdentity();
	        }
	
	        return null;
	    }
	}

You now need to tell the ServiceManager how to find the Controller Plugin and the View Helper. Add the following code in your Module.php class :

	/**
     * @return array
     */
    public function getViewHelperConfig()
    {
        return array(
            'aliases' => array(
                'userIdentity' => 'User\View\Helper\UserIdentity'
            ),
            'factories' => array(
                'Application\View\Helper\UserIdentity' => function ($serviceManager) {
                    $authenticationService = $serviceManager->getServiceLocator()
                                                            ->get('Zend\Authentication\AuthenticationService');

                    return new \Application\View\Helper\UserIdentity($authenticationService);
                }
            )
        );
    }

    /**
     * @return array
     */
    public function getControllerPluginConfig()
    {
        return array(
            'aliases' => array(
                'userIdentity' => 'User\Controller\Plugin\UserIdentity'
            ),
            'factories' => array(
                'Application\Controller\Plugin\UserIdentity' => function ($serviceManager) {
                    $authenticationService = $serviceManager->getServiceLocator()
                                                            ->get('Zend\Authentication\AuthenticationService');

                    return new \Application\Controller\Plugin\UserIdentity($authenticationService);
                }
            )
        );
    }
    
This is very simple code. This code automatically handles the dependencies with the AuthenticationService.

Here is how you use it in your controller :

	public function testAction()
	{
		$loggedUser = $this->userIdentity();
		
		if ($loggedUser === null) {
			// Nobody is logged
		} else {
			// Do whatever you want with the logged user
		}
	}
	
And in your view :

	<?php $user = $this->userIdentity(); ?>
	

### Advanced usage

DoctrineModule provides an AuthenticationAdapter factory.


## Paginator

DoctrineModule provides a simple Paginator adapter that can be used with DoctrineCollection.

> Note : if you are using Doctrine 2 ORM, what you are looking for is more likely a Paginator adapter that can be used with Doctrine 2 Paginators. Hopefully, DoctrineORMModule provides such a paginator adapter. You can find the documentation here :

### Simple example

Here is how you can use the DoctrineModule paginator adapter :

```php
use Doctrine\Common\Collections\ArrayCollection;
use DoctrineModule\Paginator\Adapter\Collection as CollectionAdapter;
use Zend\Paginator\Paginator;

// Create a Doctrine 2 Collection
$doctrineCollection = new ArrayCollection(range(1, 101));

// Create the adapter
$adapter = new CollectionAdapter($doctrineCollection);

// Create the paginator itself
$paginator = new Paginator($adapter);
$paginator->setCurrentPageNumber(1)
		  ->setItemCountPerPage(5);
		  
// Pass it to the view, and use it like a "standard" Zend paginator
```
	
For more information about Zend Paginator, please read the [Zend Paginator documentation](http://framework.zend.com/manual/2.0/en/modules/zend.paginator.introduction.html);

	

