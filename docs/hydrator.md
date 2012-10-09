## Hydrator

Hydrators are simple objects that allow to convert an array of data to an object (this is called "hydrating") and to convert back an object to an array (this is called "extracting"). Hydrators are mainly used in the context of Forms, with our new binding functionality. If you are not really comfortable with hydrators, please first read [Zend Framework hydrator's documentation](http://framework.zend.com/manual/2.0/en/modules/zend.stdlib.hydrator.html)


### Basic usage

DoctrineModule ships with a very powerful hydrator that allow almost any use-case.

#### Example 1 : simple example

Let's begin by a simple example:

```php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class City
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
     * @ORM\Column(type="string", length=8)
     */
    protected $postCode;

    public function getId()
    {
   		return $this->id;
    }

    public function setName($name)
    {
    	$this->name = $name;
    }

    public function getName()
    {
    	return $this->name;
    }

    public function setPostCode($postCode)
    {
    	$this->postCode = $postCode;
    }

    public function getPostCode()
    {
    	return $this->postCode;
    }
}
```

Now, let's use the Doctrine hydrator :

```php
$hydrator = new \DoctrineModule\Stdlib\Hydrator\DoctrineObject($entityManager);
$city = new City();
$data = array(
	'name' => 'Paris',
	'postCode' => '75016'
);

$city = $hydrator->hydrate($data, $city);

echo $city->getName(); // prints "Paris"
echo $city->getPostCode(); // prints "75016"

$dataArray = $hydrator->extract($city);
echo $dataArray['city']; // prints "Paris"
echo $dataArray['postCode']; // prints "75016"
```

Internally, DoctrineModule's hydrator uses by default a `Zend\Stdlib\Hydrator\ClassMethods` hydrator, meaning that the
hydrator call getter and setters for extracting and hydrating, respectively. This is why the keys of the data, and the
property names have to match.

You can change the default hydrator used internally by calling the `setHydrator()`, or directly during construction:

```php
$objectPropertyHydrator = new \Zend\Stdlib\Hydrator\ObjectProperty();

// Using constructor:
$doctrineHydrator = new \DoctrineModule\Stdlib\Hydrator\DoctrineObject($entityManager, $objectPropertyHydrator);

// Using function
$doctrineHydrator->setHydrator($objectPropertyHydrator);
```

As you can see from this example, in such simple cases, DoctrineModule hydrator brings nearly no advantages over a "simpler"
ClassMethods Zend hydrator. However, even in those cases, I recommend you to use the DoctrineModule hydrator, as if a field is
of type datetime/time/date, the hydrator can automatically converts a timestamp to a DateTime object.

#### Example 2 : OneToOne relationship

DoctrineModule hydrator is especially useful when dealing with relations (OneToOne, OneToMany, ManyToOne). For instance, let's
add an Address entity that composes the City entity described earlier.

```php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Address
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
	protected $street;

	/**
     * @ORM\OneToOne(targetEntity="City")
     */
	protected $city;

    public function getId()
    {
   		return $this->id;
    }

    public function setStreet($street)
    {
    	$this->street = $street;
    }

    public function getStreet()
    {
    	return $this->street;
    }

    public function setCity(City $city)
    {
    	$this->city = $city;
    }

    public function getCity()
    {
    	return $this->city;
    }
}
```

Once again, let's use the Doctrine hydrator:

```php
$hydrator = new \DoctrineModule\Stdlib\Hydrator\DoctrineObject($entityManager);
$address = new Address();
$city = new City();
$city->setName('Paris')
	 ->setPostCode('75016');

$data = array(
	'street' => '1 avenue des Champs Elysees',
	'city' => $city
);

$address = $hydrator->hydrate($data, $address);

echo $address->getStreet(); // prints "1 avenue des Champs Elysees"
echo $address->getCity()->getPostCode(); // prints "75016"
```

This can perfectly be achieved with using the standard ClassMethods hydrator. But let's not say that the cities are already
saved in databases, and that we want to be able to set the city of the address only using the city's identifier (this is a
common pattern in Forms, where we use hidden inputs to store an identifier). This can be achieved easily with Doctrine
hydrator:

```php
$hydrator = new \DoctrineModule\Stdlib\Hydrator\DoctrineObject($entityManager);
$address = new Address();

$data = array(
	'street' => '1 avenue des Champs Elysees',
	'city' => '2' // we assume '2' is the Id of Paris
);

$address = $hydrator->hydrate($data, $address);

echo $address->getStreet(); // prints "1 avenue des Champs Elysees"
$address->getCity()->getName(); // prints "Paris"
$address->getCity()->getPostCode(); // prints "75016"
```

#### Example 3 : OneToMany relationship

DoctrineModule hydrator also handles OneToMany relationships (when use `Zend\Form\Element\Collection` element). Please refer
to the official [Zend Framework 2 documentation](http://framework.zend.com/manual/2.0/en/modules/zend.form.collections.html) to
learn more about Collection.

Please refer to the Cookbook in this page to have a fully example of such relationships.

> Note: internally, for a given collection, if an array contains identifiers, the hydrator automatically fetch the objects throuhg the Doctrine `find` function. However, this may cause problems if one of the value of the collection is the empty string '' (as the ``find`` will most likely fail). In order to solve this problem, empty string identifiers are simply ignored during the hydration phase. Therefore, if your database contains an empty string value as primary key, the hydrator could not work correctly (the simplest way to avoid that is simply to not have an empty string primary key, which should not happen if you use auto-increment primary keys, anyway).


### Advanced use

When dealing with Forms, the following use-case often appears:

1. Collection elements are created and persisted to the database (for instance, a list of tags for an article).
2. An edit form allow to delete existing elements in the collection, or add new elements (or even modify existing ones).

This use case can quickly become leads to a lot of boilerplate code. Hopefully, DoctrineModule hydrator make it so easy you
will find this black magic !

To make this happen, you need to slightly modify both your entity code and your forms. First, about the entity. Let's take again
the simple Article / Tags example. The Article therefore has a OneToMany or ManyToMany relationships, and must likely have such
a setter:

```php
public function setTags(ArrayCollection $tags)
{
	$this->tags = $tags;
}
```

This has to be changed to:

```php
use DoctrineModule\Util\CollectionUtils;

public function setTags(ArrayCollection $tags)
{
	$this->tags = CollectionUtils::intersectionUnion($this->tags, $tags);
}
```



### Performance considerations

Although using the hydrator is like magical as it abstracts most of the tedious task, you have to be aware that it can leads to performance issues in some situations. Please carefully read the following paragraphs in order to know how to solve (and avoid !) them.

#### Make hydrator get a reference instead of a database call

By default, the DoctrineModule hydrator performs a "find" operation for every relationships, and hence retrieving the whole entity from database. This is not always the wanted behaviour (but it can be !), and it can leads to performance problems. Most of the time, what you want is just retrieving a reference to this object, instead of fetching it from database.

If you are using Doctrine 2 ORM, you have to use the hydrator from DoctrineORMModule (instead of the one from DoctrineModule). The usage is exactly the same, except that instead of a `find` call, it makes a `getReference` call. This is up to you to choose the right hydrator for your specific need.

#### Hydration Strategies

The hydrator implements Zend Framework 2's StrategyEnabledInterface which allows you to inspect and modify data before it is processed by the Hydrator. Please note that hydration strategies will only be applied to the hydrate() function and not extract() as this is proxied directly to the default Hydrator, in which case you should do the following:

```php
$doctrineHydrator->getHydrator()->addStrategy(new MyHydrationStrategy());
```

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