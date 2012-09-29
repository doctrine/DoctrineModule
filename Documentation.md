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

```php
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
}
```
		
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

```php
$authOptions = new AuthenticationAdapterOptions(array(
	'object_manager' => 'Doctrine\ORM\EntityManager',
	'identity_class' => 'Application\Entity\User',
	'identity_property' => 'email'
	'credential_property' => 'password',
	'credential_callable' => function(User $user, $passwordGiven) {
		return ($user->getPassword() === crypt($passwordGiven, $user->getPassword()));
	}
));
```


#### Using the AuthenticationService

Now that we have defined how to create a `Zend\Authentication\AuthenticationService` object, we can use it in our code. Here is an example of you we could use it from a controller action :

```php
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
	
The storage automatically extracts ONLY the identifier values and only store this in session. Later, when you want to retrieve the logged user :

```php
$authenticationService = $this->serviceLocator()->get('Zend\Authentication\AuthenticationService');
$loggedUser = $authenticationService->getIdentity();
```
	
The storage will automatically make the database call with the entity identifiers, and effectively return you a fully loaded entity.

#### View helper and controller helper

Knowing if a user is logged and get the logged user is a very common practice that is done both in controller and views. That's why, most often, you will create a View Helper and a Controller Plugin to easily get the logged user.

Here is a sample code that shows you the Controller Plugin :

```php
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
```
	
The View Helper is very similar :

```php
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
```

You now need to tell the ServiceManager how to find the Controller Plugin and the View Helper. Add the following code in your Module.php class :

```php
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
```
    
This is very simple code. This code automatically handles the dependencies with the AuthenticationService.

Here is how you use it in your controller :

```php
public function testAction()
{
	$loggedUser = $this->userIdentity();
	
	if ($loggedUser === null) {
		// Nobody is logged
	} else {
		// Do whatever you want with the logged user
	}
}
```
	
And in your view :

```php
<?php $user = $this->userIdentity(); ?>
```
	

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
	
For more information about Zend Paginator, please read the [Zend Paginator documentation](http://framework.zend.com/manual/2.0/en/modules/zend.paginator.introduction.html).

	
## Validator

DoctrineModule provides two validators that work out the box : `DoctrineModule\Validator\ObjectExists` and `DoctrineModule\Validator\NoObjectExists` that allow to check if an entity or does not exists in database, respectively. They work like any other standard Zend validators.

Both validators accept the following options :

* `object_repository` : an instance of an object repository.
* `fields` : an array that contains all the fields that are used to check if the entity exists (or does not).

> Tip : to get an object repository (in Doctrine ORM this is called an entity repository) from an object manager (in Doctrine ORM this is called an entity manager), you need to call the `getRepository` function of any valid object manager instance, passing it the FQCN of the class. For instance, in the context of Doctrine 2 ORM, here is how you get the `object_repository` of the 'Application\Entity\User' entity :

```php
$repository = $entityManager->getRepository('Application\Entity\User');
```

### Simple usage

Here is how you would add a `NoObjectExists` validator to a form element (for more details about Form, please refer to the official Zend Framework 2 documentation) :

```php
namespace Application\Form;

use DoctrineModule\Validator\NoObjectExists as NoObjectExistsValidator;
use Zend\Form\Form;
use Zend\ServiceManager\ServiceManager;

class User extends Form
{		
	public function __construct(ServiceManager $serviceManager)
	{			
		parent::__construct('my-form');
		
		// Add an element
		$this->add(array(
            'type'    => 'Zend\Form\Element\Email',
            'name'    => 'email',
            'options' => array(
                'label' => 'Email'
            ),
            'attributes' => array(
                'required'  => 'required'
            )
       	));
       	
       	// add other elements (submit, CSRF…)
       	
       	// Fetch any valid object manager from the Service manager (here, an entity manager)
       	$entityManager = $serviceManager->get('Doctrine\ORM\EntityManager');
       	
       	// Now get the input filter of the form, and add the validator to the email input
       	$emailInput = $this->getInputFilter()->get('email');
       	
       	$noObjectExistsValidator = new NoObjectExistsValidator(array(
            'object_repository' => $entityManager->getRepository('Application\Entity\User'),
            'fields'            => 'email'
       	));

       	$emailInput->getValidatorChain()
                      ->addValidator($noObjectExistsValidator);
	}
}
```

Of course, if you are using fieldsets, you can directly add the validator using the array notation, for instance in the `getInputFilterSpecification` function, as shown here :

```php
namespace Application\Form;

use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\ServiceManager\ServiceManager;

class UserFieldset extends Fieldset implements InputFilterProviderInterface
{
	protected $serviceManager;
	
	public function __construct(ServiceManager $serviceManager)
	{
		$this->serviceManager = $serviceManager;
		
		parent::__construct('my-fieldset');
		
		// Add an element
		$this->add(array(
            'type'    => 'Zend\Form\Element\Email',
            'name'    => 'email',
            'options' => array(
                'label' => 'Email'
            ),
            'attributes' => array(
                'required'  => 'required'
            )
       	));
	}
	
	public function getInputFilterSpecification()
	{
		$entityManager = $this->serviceManager->get('Doctrine\ORM\EntityManager');
		
		return array(
			'email' => array(
				'validators' => array(
					array(
						'name' => 'DoctrineModule\Validator\NoObjectExists',
						'options' => array(
							'object_manager' => $entityManager->getRepository('Application\Entity\User'),
							'fields' => 'email'
						)
					)
				)
			)
		);
	}
}
```

You can change the default message of the validators the following way :

```php

// For NoObjectExists validator (using array notation) :
'validators' => array(
	array(
		'name' => 'DoctrineModule\Validator\NoObjectExists',
		'options' => array(
			'object_manager' => $this->getEntityManager()->getRepository('Application\Entity\User'),
			'fields' => 'email'
		),
		**'messages' => array(
			'objectFound' => 'Sorry guy, a user with this email already exists !'
		)**
	)
)

// For ObjectExists validator (using object notation) :
$objectExistsValidator = new \DoctrineModule\Validator\ObjectExists(array(
	'object_repository' => $entityManager->getRepository('Application\Entity\User'),
    'fields'            => 'email'
));

**$objectExistsValidator->setMessage('noObjectFound', 'Sorry, we expect that this email exists !');**
```

> Note : as you can see, in order to create a validator in your form objects, you need an object repository, and hence you need to have access to the service manager in order to fetch it (this is also the case for other features from DoctrineModule like custom Form elements). However, when dealing with complex forms, you can have a very deep hierarchy of fieldsets, and "transferring" the service manager from one fieldset to another can be a tedious task, and bring useless complexity to your code, especially if only the deepest fieldset effectively needs the service manager. When dealing with such cases, I have found that the simplest case is to use a Registry. I perfectly know that registry was removed from Zend Framework 2, and it is considered bad practice as it makes testing harder. However, for this very specific use case, I found that this is a nice way to solve the problem. But remember, don't tend to take the easy way out, and don't use this Registry trick every where in your program.


## Hydrator

Hydrators are simple objects that allow to convert an array of data to an object (this is called "hydrating") and to convert back an object to an array (this is called "extracting"). Hydrators are mainly used in the context of Forms, with our new binding functionnality. If you are not really comfortable with hydrators, please first read [Zend Framework hydrator's documentation](http://framework.zend.com/manual/2.0/en/modules/zend.stdlib.hydrator.html)


### Basic usage

DoctrineModule ships with a very powerful hydrator that allow almost any use-case. Before digging into this component, you have to understand why you would need such a hydrator.

### Advanced use


### Performance considerations

Although using the hydrator is like magical as it abstracts most of the tedious task, you have to be aware that it can leads to performance issues in some situations. Please carefully read the following paragraphs in order to know how to solve (and avoid !) them.

#### Make hydrator get a reference instead of a database call

By default, the DoctrineModule hydrator performs a "find" operation for every relationships, and hence retrieving the whole entity from database. This is not always the wanted behaviour (but it can be !), and it can leads to performance problems. Most of the time, what you want is just retrieving a reference to this object, instead of fetching it from database.

If you are using Doctrine 2 ORM, you have to use the hydrator from DoctrineORMModule (instead of the one from DoctrineModule). The usage is exactly the same, except that instead of a `find` call, it makes a `getReference` call. This is up to you to choose the right hydrator for your specific need.

#### Unwanting side-effect

You have to be very careful when you are using DoctrineModule hydrator with complex entities that contain a lot of relationships, as a lot of unnecessary calls to database can be made if you are not perfectly aware of what happen under the hood. To explain this problem, let's have an example.

Imagine the following entity :


```php
namespace Application\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="Students")
 */
class User
{
	/**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string", length=48)
     */
	protected $name;
	
    /**
     * @ORM\OneToOne(targetEntity="City")
     */
    protected $city;
    
    // … getter and setters are defined …
}
```

This simple entity contains an id, a string property, and a OneToOne relationship. If you are using Zend Framework 2 forms the correct way, you will likely have a fieldset for every entity, so that you have a perfect mapping between entities and fieldsets. Here are fieldsets for User and and City entities.

> If you are not comfortable with Fieldsets and how they should work, please refer to [this part of Zend Framework 2 documentation](http://framework.zend.com/manual/2.0/en/modules/zend.form.collections.html).

First the User fieldset :

```php
namespace Application\Form;

use Application\Entity\User;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\ServiceManager\ServiceManager;

class UserFieldset extends Fieldset implements InputFilterProviderInterface
{
	public function __construct(ServiceManager $serviceManager)
	{
		parent::__construct('user');
		$entityManager = $serviceManager->get('Doctrine\ORM\EntityManager');
		
		$this->setHydrator(new DoctrineHydrator($entityManager))
			 ->setObject(new User());
		
		$this->add(array(
            'type'    => 'Zend\Form\Element\Text',
            'name'    => 'name',
            'options' => array(
                'label' => 'Your name'
            ),
            'attributes' => array(
                'required'  => 'required'
            )
       	));
       	
       	$cityFieldset = new CityFieldset($serviceManager);
       	$cityFieldset->setLabel('Your city');
       	$cityFieldset->setName('city');
       	$this->add($cityFieldset);
	}
	
	public function getInputFilterSpecification()
	{
		return array(
			'name' => array(
				'required' => true
			)
		);
	}
}

```

And then the City fieldset :

```php
namespace Application\Form;

use Application\Entity\City;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\ServiceManager\ServiceManager;

class CityFieldset extends Fieldset implements InputFilterProviderInterface
{
	public function __construct(ServiceManager $serviceManager)
	{
		parent::__construct('city');
		$entityManager = $serviceManager->get('Doctrine\ORM\EntityManager');
		
		$this->setHydrator(new DoctrineHydrator($entityManager))
			 ->setObject(new City());
		
		$this->add(array(
            'type'    => 'Zend\Form\Element\Text',
            'name'    => 'name',
            'options' => array(
                'label' => 'Name of your city'
            ),
            'attributes' => array(
                'required'  => 'required'
            )
       	));
       	
       	$this->add(array(
            'type'    => 'Zend\Form\Element\Text',
            'name'    => 'postCode',
            'options' => array(
                'label' => 'Postcode of your city'
            ),
            'attributes' => array(
                'required'  => 'required'
            )
       	));
	}
	
	public function getInputFilterSpecification()
	{
		return array(
			'name' => array(
				'required' => true
			),
			
			'postCode' => array(
				'required' => true
			)
		);
	}
}

```

Now, let's say that we have one form where a logged user can only change his name. This specific form does not allow the user to change this city, and the fields of the city are not even rendered in the form. Naïvely, this form would be like this :

```php
namespace Application\Form;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Form\Form;
use Zend\ServiceManager\ServiceManager;

class EditNameForm extends Form
{
	public function __construct(ServiceManager $serviceManager)
	{
		parent::__construct('edit-name-form');
		$entityManager = $serviceManager->get('Doctrine\ORM\EntityManager');
		
		$this->setHydrator(new DoctrineHydrator($entityManager));
		
		// Add the user fieldset, and set it as the base fieldset
		$userFieldset = new UserFieldset($serviceManager);
		$userFieldset->setName('user');
		$userFieldset->setUseAsBaseFieldset(true);
		$this->add($userFieldset);
		
		// … add CSRF and submit elements …
		
		// Set the validation group so that we don't care about city
		$this->setValidationGroup(array(
			'csrf', // assume we added a CSRF element
			'user' => array(
				'name'
			)		
		));
	}
}
```

> Once again, if you are not familiar with the concepts here, please read the [official documentation about that](http://framework.zend.com/manual/2.0/en/modules/zend.form.collections.html).

Here, we create a simple form called "EditSimpleForm". Because we set the validation group, all the inputs related to city (postCode and name of the city) won't be validated, which is exactly what we want. The action will look something like this :

```php
public function editNameAction()
{
	// Create the form
	$form = new EditNameForm();
	
	// Get the logged user (for more informations about userIdentity(), please read the Authentication doc)
	$loggedUser = $this->userIdentity();
	
	// We bind the logged user to the form, so that the name is pre-filled with previous data
	$form->bind($loggedUser);
	
	$request = $this->request;
	if ($request->isPost()) {
		// Set data from post
		$form->setData($request->getPost());
		
		if ($form->isValid()) {
			// You can now safely save $loggedUser
		}
	}
}
```

This looks good, isn't it ? However, if we check the queries that are made (for instance using the awesome [ZendDeveloperTools module](https://github.com/zendframework/ZendDeveloperTools)), we will see that a request is made to fetch data for the City relationship of the user, and we hence have a completely useless database call, as this information is not rendered by the form.

You could ask, why ? Yes, we set the validation group, BUT the problem happens during the extracting phase. Here is how it works : when an object is bound to the form, this latter iterates through all its fields, and tries to extract the data from the object that is bound. In our example, here is how it work :

1. It first arrives to the UserFieldset. The input are "name" (which is string field), and a "city" which is another fieldset (in our User entity, this is a OneToOne relationship to another entity). The hydrator will extract both the name and the city (which will be a Doctrine 2 Proxy object).
2. Because the UserFieldset contains a reference to another Fieldset (in our case, a CityFieldset), it will, in turn, tries to extract the values of the City to populate the values of the CityFieldset. And here is the problem : City is a Proxy, and hence because the hydrator tries to extract its values (the name and postcode field), Doctrine will automatically fetch the object from the database in order to please the hydrator.

This is absolutely normal, this is how ZF 2 forms work and what make them nearly magic, but in this specific case, it can leads to desastrous consequences. When you have very complex entities with a lot of OneToMany collections, imagine how many unnecessary calls can be made (actually, after discovering this problem, I've realized that my applications was doing 10 unnecessary database calls).

In fact, the fix is ultra simple : if you don't need specific fieldsets in a form, remove them. Here is the fix EditUserForm :

```php
namespace Application\Form;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Form\Form;
use Zend\ServiceManager\ServiceManager;

class EditNameForm extends Form
{
	public function __construct(ServiceManager $serviceManager)
	{
		parent::__construct('edit-name-form');
		$entityManager = $serviceManager->get('Doctrine\ORM\EntityManager');
		
		$this->setHydrator(new DoctrineHydrator($entityManager));
		
		// Add the user fieldset, and set it as the base fieldset
		$userFieldset = new UserFieldset($serviceManager);
		$userFieldset->setName('user');
		$userFieldset->setUseAsBaseFieldset(true);
		
		// We don't want City relationship, so remove it !!
		$userFieldset->remove('city');
		
		$this->add($userFieldset);
		
		// … add CSRF and submit elements …
		
		// We don't even need the validation group as the City fieldset does not
		// exist anymore
	}
}
```

And boom ! As the UserFieldset does not contain the CityFieldset relation anymore, it won't be extracted !

As a rule of thumb, try to remove any unnecessary fieldset relationship, and always look at which database calls are made.