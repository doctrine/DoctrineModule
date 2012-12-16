## Validator

DoctrineModule provides three validators that work out the box : `DoctrineModule\Validator\ObjectExists` and `DoctrineModule\Validator\NoObjectExists` that allow to check if an entity or does not exists in database, respectively; `DoctrineModule\Validator\UniqueObject` that allows to check if a value is only used in one object. They work like any other standard Zend validators.

All three validators accept the following options :

* `object_repository` : an instance of an object repository.
* `fields` : an array that contains all the fields that are used to check if the entity exists (or does not).

The `DoctrineModule\Validator\UniqueObject` also needs the following option:

* `object_manager` : an instance of an object manager.

> Tip : to get an object repository (in Doctrine ORM this is called an entity repository) from an object manager (in Doctrine ORM this is called an entity manager), you need to call the `getRepository` function of any valid object manager instance, passing it the FQCN of the class. For instance, in the context of Doctrine 2 ORM, here is how you get the `object_repository` of the 'Application\Entity\User' entity :

```php
$repository = $entityManager->getRepository('Application\Entity\User');
```

### Simple usage

You can directly instantiate a validator the following way:

```php
$validator = new \DoctrineModule\Validator\ObjectExists(array(
    'object_repository' => $objectManager->getRepository('Application\Entity\User'),
    'fields' => array('email')
));

var_dump($validator->isValid('test@example.com')); // dumps 'true' if an entity matches
var_dump($validator->isValid(array('email' => 'test@example.com'))); // dumps 'true' if an entity matches
```

### Use together with Zend Framework 2 forms

Of course, validators are especially useful together with forms. And this is deadly simple. Here is how you would add a `NoObjectExists` validator to a form element (for more details about Form, please refer to the official Zend Framework 2 documentation) :

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