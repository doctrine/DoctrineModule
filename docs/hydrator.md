## Hydrator

Hydrators are simple objects that allow to convert an array of data to an object (this is called "hydrating") and to
convert back an object to an array (this is called "extracting"). Hydrators are mainly used in the context of Forms,
with the new binding functionality of Zend Framework 2, but can also be used for any hydrating/extracting context (for
instance, it can be used in RESTful context). If you are not really comfortable with hydrators, please first
read [Zend Framework hydrator's documentation](http://framework.zend.com/manual/2.0/en/modules/zend.stdlib.hydrator.html).


### Basic usage

DoctrineModule ships with a very powerful hydrator that allow almost any use-case.

#### Create a hydrator

To create a Doctrine Hydrator, you just need one thing: an object manager (also called Entity Manager in Doctrine ORM
or Document Manager in Doctrine ODM):

```php
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

$hydrator = new DoctrineHydrator($objectManager);
```

Starting from DoctrineModule 0.8.0, a hydrator can be used for multiple objects.

The hydrator constructor also allows a second parameter, `byValue`, which is true by default. We will come back later
about this distinction, but to be short, it allows the hydrator the change the way it gets/sets data by either
accessing the public API of your entity (getters/setters) or directly get/set data through reflection, hence bypassing
any of your custom logic.

#### Example 1 : simple entity with no associations

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
}
```

Now, let's use the Doctrine hydrator :

```php
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

$hydrator = new DoctrineHydrator($entityManager);
$city = new City();
$data = array(
	'name' => 'Paris'
);

$city = $hydrator->hydrate($data, $city);

echo $city->getName(); // prints "Paris"

$dataArray = $hydrator->extract($city);
echo $dataArray['name']; // prints "Paris"
```

As you can see from this example, in simple cases, the DoctrineModule hydrator provides nearly no benefits over a
simpler hydrator like "ClassMethods". However, even in those cases, I suggest you to use it, as it performs automatic
conversions between types. For instance, it can convert timestamp to DateTime (which is the type used by Doctrine to
represent dates):

```php

namespace Application\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Appointment
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $time;

    public function getId()
    {
	return $this->id;
    }

    public function setTime(DateTime $time)
    {
    	$this->time = $time;
    }

    public function getTime()
    {
    	return $this->time;
    }
}
```

Let's use the hydrator:

```php
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

$hydrator = new DoctrineHydrator($entityManager);
$appointment = new Appointment();
$data = array(
	'time' => '1357057334'
);

$appointment = $hydrator->hydrate($data, $appointment);

echo get_class($appointment->getTime()); // prints "DateTime"
```

As you can see, the hydrator automatically converted the timestamp to a DateTime object during the hydration, hence
allowing us to have a nice API in our entity with correct typehint.


#### Example 2 : OneToOne/ManyToOne associations

DoctrineModule hydrator is especially useful when dealing with associations (OneToOne, OneToMany, ManyToOne) and
integrates nicely with the Form/Fieldset logic ([learn more about this here](http://framework.zend.com/manual/2.0/en/modules/zend.form.collections.html)).

Let's take a simple example with a BlogPost and a User entity to illustrate OneToOne association:

```php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
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
    protected $username;

    /**
     * @ORM\Column(type="string")
     */
    protected $password;

    public function getId()
    {
	return $this->id;
    }

    public function setUsername($username)
    {
    	$this->username = $username;
    }

    public function getUsername()
    {
    	return $this->username;
    }

    public function setPassword($password)
    {
    	$this->password = $password;
    }

    public function getPassword()
    {
    	return $this->password;
    }
}
```

And the BlogPost entity, with a ManyToOne association:

```php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class BlogPost
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\User")
     */
    protected $user;

    /**
     * @ORM\Column(type="string")
     */
    protected $title;

    public function getId()
    {
        return $this->id;
    }

    public function setUser(User $user)
    {
    	$this->user = $user;
    }

    public function getUser()
    {
    	return $this->user;
    }

    public function setTitle($title)
    {
    	$this->title = $title;
    }

    public function getTitle()
    {
    	return $this->title;
    }
}
```

There are two use cases that can arise when using OneToOne association: the toOne entity (in the case, the user) may
already exist (which will often be the case with a User and BlogPost example), or it can be created too. The
DoctrineHydrator natively supports both cases.

##### Existing entity in the association

When the association's entity already exists, what you need to do is simply giving the identifier of the association:

```php
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

$hydrator = new DoctrineHydrator($entityManager);
$blogPost = new BlogPost();
$data = array(
	'title' => 'The best blog post in the world !',
	'user'  => array(
		'id' => 2 // Written by user 2
	)
);

$blogPost = $hydrator->hydrate($data, $blogPost);

echo $blogPost->getTitle(); // prints "The best blog post in the world !"
echo $blogPost->getUser()->getId(); // prints 2
```

**NOTE** : when using association whose primary key is not compound, you can rewrite the following more succinctly:

```php
$data = array(
	'title' => 'The best blog post in the world !',
	'user'  => array(
		'id' => 2 // Written by user 2
	)
);
```

to:

```php
$data = array(
	'title' => 'The best blog post in the world !',
	'user'  => 2
);
```


##### Non-existing entity in the association

If the association's entity does not exist, you just need to give the given object:

```php
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

$hydrator = new DoctrineHydrator($entityManager);
$blogPost = new BlogPost();
$user = new User();
$user->setUsername('bakura');
$user->setPassword('p@$$w0rd');

$data = array(
	'title' => 'The best blog post in the world !',
	'user'  => $user
);

$blogPost = $hydrator->hydrate($data, $blogPost);

echo $blogPost->getTitle(); // prints "The best blog post in the world !"
echo $blogPost->getUser()->getId(); // prints 2
```

For this to work, you must also slightly change your mapping, so that Doctrine can persist new entities on
associations (note the cascade options on the OneToMany association):

```php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class BlogPost
{
    /** .. */

    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\User", cascade={"persist"})
     */
    protected $user;

    /** … */
}
```

It's also possible to use a nested fieldset for the User data.  The hydrator will
use the mapping data to determine the identifiers for the toOne relation and either
attempt to find the existing record or instanciate a new target instance which will
be hydrated before it is passed to the BlogPost entity.

**NOTE** : you're not really allowing users to be added via a blog post, are you?

```php
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

$hydrator = new DoctrineHydrator($entityManager, 'Application\Entity\BlogPost');
$blogPost = new BlogPost();

$data = array(
    'title' => 'Art thou mad?',
    'user' => array(
        'id' => '',
        'username' => 'willshakes',
        'password' => '2BorN0t2B'
    )
);

$blogPost = $hydrator->hydrate($data, $blogPost);

echo $blogPost->getUser()->getUsername(); // prints willshakes
echo $blogPost->getUser()->getPassword(); // prints 2BorN0t2B
```


#### Example 3 : OneToMany association

DoctrineModule hydrator also handles OneToMany relationships (when use `Zend\Form\Element\Collection` element). Please
refer to the official [Zend Framework 2 documentation](http://framework.zend.com/manual/2.0/en/modules/zend.form.collections.html) to learn more about Collection.

> Note: internally, for a given collection, if an array contains identifiers, the hydrator automatically fetch the
objects through the Doctrine `find` function. However, this may cause problems if one of the value of the collection
is the empty string '' (as the ``find`` will most likely fail). In order to solve this problem, empty string identifiers
are simply ignored during the hydration phase. Therefore, if your database contains an empty string value as primary
key, the hydrator could not work correctly (the simplest way to avoid that is simply to not have an empty string primary
key, which should not happen if you use auto-increment primary keys, anyway).

Let's take again a simple example: a BlogPost and Tag entities.

```php

namespace Application\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class BlogPost
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="Application\Entity\Tag", mappedBy="blogPost")
     */
    protected $tags;

    /**
     * Never forget to initialize all your collections !
     */
	public function __construct()
	{
		$this->tags = new ArrayCollection();
	}

    public function getId()
    {
   		return $this->id;
    }

	public function addTags(Collection $tags)
	{
		foreach ($tags as $tag) {
			$tag->setBlogPost($this);
			$this->tags->add($tag);
		}
	}

	public function removeTags(Collection $tags)
	{
		foreach ($tags as $tag) {
			$tag->setBlogPost(null);
			$this->tags->removeElement($tag);
		}
	}

    public function getTags()
    {
    	return $this->tags;
    }
}
```

And the Tag entity:

```php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Tag
{
	/**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\BlogPost", inversedBy="tags")
     */
	protected $blogPost;

	/**
	 * @ORM\Column(type="string")
	 */
	protected $name;

    public function getId()
    {
   		return $this->id;
    }

	/**
	 * Allow null to remove association
	 */
	public function setBlogPost(BlogPost $blogPost = null)
	{
		$this->blogPost = $blogPost;
	}

    public function getBlogPost()
    {
    	return $this->blogPost;
    }

    public function setName($name)
    {
    	$this->name = $name;
    }

    public function getName()
    {
    	return $this->name;
    }
}
```

Please note interesting things in BlogPost entity. We have defined two functions: addTags and removeTags. Those
functions must be always defined and are called automatically by Doctrine hydrator when dealing with collections.
You may think this is overkill, and ask why you cannot just define a `setTags` function to replace the old collection
by the new one:

```php
public function setTags(Collection $tags)
{
	$this->tags = $tags;
}
```

But this is very bad, because Doctrine collections should not be swapped, mostly because collections are managed by
an ObjectManager, thus it must not be replaced by a new instance.

Once again, two cases may arise: the tags already exist or they does not.

##### Existing entity in the association

When the association's entity already exists, what you need to do is simply giving the identifiers of the entities:

```php
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

$hydrator = new DoctrineHydrator($entityManager);
$blogPost = new BlogPost();
$data = array(
	'title' => 'The best blog post in the world !',
	'tags'  => array(
		array('id' => 3), // add tag whose id is 3
		array('id' => 8)  // also add tag whose id is 8
	)
);

$blogPost = $hydrator->hydrate($data, $blogPost);

echo $blogPost->getTitle(); // prints "The best blog post in the world !"
echo count($blogPost->getTags()); // prints 2
```

**NOTE** : once again, this:

```php
$data = array(
	'title' => 'The best blog post in the world !',
	'tags'  => array(
		array('id' => 3), // add tag whose id is 3
		array('id' => 8)  // also add tag whose id is 8
	)
);
```

can be written:

```php
$data = array(
	'title' => 'The best blog post in the world !',
	'tags'  => array(3, 8)
);
```

##### Non-existing entity in the association

If the association's entity does not exist, you just need to give the given object:

```php
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

$hydrator = new DoctrineHydrator($entityManager);
$blogPost = new BlogPost();

$tags = array();

$tag1 = new Tag();
$tag1->setName('PHP');
$tags[] = $tag1;

$tag2 = new Tag();
$tag2->setName('STL');
$tags[] = $tag2;

$data = array(
	'title' => 'The best blog post in the world !',
	'tags'  => $tags // Note that you can mix integers and entities without any problem
);

$blogPost = $hydrator->hydrate($data, $blogPost);

echo $blogPost->getTitle(); // prints "The best blog post in the world !"
echo count($blogPost->getTags()); // prints 2
```

For this to work, you must also slightly change your mapping, so that Doctrine can persist new entities on
associations (note the cascade options on the OneToMany association):

```php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class BlogPost
{
	/** .. */

    /**
     * @ORM\OneToMany(targetEntity="Application\Entity\Tag", mappedBy="blogPost", cascade={"persist"})
     */
	protected $tags;

	/** … */
}
```

##### Handling of null values

When a null value is passed to a OneToOne or ManyToOne field, for example;

```php
$data = array(
    'city' => null
);
```

The hydrator will check whether the setCity() method on the Entity allows null values and acts accordingly, the following describes the process that happens when a null value is received:

1. If the setCity() method DOES NOT allow null values i.e. `function setCity(City $city)`, the null is silently ignored and will not be hydrated.
2. If the setCity() method DOES allow null values i.e. `function setCity(City $city = null)`, the null value will be hydrated.

### Collections strategy

By default, every collections association has a special strategy attached to it that is called during the hydrating
and extracting phase. All those strategies extend from the class
`DoctrineModule\Stdlib\Hydrator\Strategy\AbstractCollectionStrategy`.

DoctrineModule provides two strategies out of the box:

1. `DoctrineModule\Stdlib\Hydrator\Strategy\AllowRemoveByValue`: this is the default strategy, it removes old elements that are not in the new collection.
2. `DoctrineModule\Stdlib\Hydrator\Strategy\AllowRemoveByReference`: this is the default strategy (if set to byReference), it removes old elements that are not in the new collection.
3. `DoctrineModule\Stdlib\Hydrator\Strategy\DisallowRemoveByValue`: this strategy does not remove old elements even if they are not in the new collection.
4. `DoctrineModule\Stdlib\Hydrator\Strategy\DisallowRemoveByReference`: this strategy does not remove old elements even if they are not in the new collection.

As a consequence, when using `AllowRemove*`, you need to define both adder (eg. addTags) and remover (eg. removeTags).
On the other hand, when using the `DisallowRemove*` strategy, you must always define at least the adder, but the remover
is optional (because elements are never removed).

The following table illustrate the difference between the two strategies

| Strategy | Initial collection | Submitted collection | Result |
| -------- | ------------------ | -------------------- | ------ |
| AllowRemove* | A, B  | B, C | B, C
| DisallowRemove* | A, B  | B, C | A, B, C

The difference between ByValue and ByReference is that when using strategies that end by ByReference, it won't use
the public API of your entity (adder and remover) - you don't even need to define them -. It will directly add and
remove elements directly from the collection.


#### Changing the strategy

Changing the strategy for collections is plain easy.

```php
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use DoctrineModule\Stdlib\Hydrator\Strategy;

$hydrator = new DoctrineHydrator($entityManager);
$hydrator->addStrategy('tags', new Strategy\DisallowRemoveByValue());
```

Note that you can also add strategies to simple fields.


### By value and by reference

By default, Doctrine Hydrator works by value. This means that the hydrator will access and modify your properties
through the public API of your entities (that is to say, with getters and setters). However, you can override this
behaviour to work by reference (that is to say that the hydrator will access the properties through Reflection API,
and hence bypass any logic you may include in your setters/getters).

To change the behaviour, just give the third parameter of the constructor to false:

```php
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

$hydrator = new DoctrineHydrator($objectManager, false);
```

To illustrate the difference between, the two, let's do an extraction with the given entity:

```php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SimpleEntity
{
    /**
     * @ORM\Column(type="string")
     */
	protected $foo;

	public function getFoo()
	{
		die();
	}

  	/** ... */
}
```

Let's now use the hydrator using the default method, by value:

```php
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

$hydrator = new DoctrineHydrator($objectManager);
$object   = new SimpleEntity();
$object->setFoo('bar');

$data = $hydrator->extract($object);

echo $data['foo']; // never executed, because the script was killed when getter was accessed
```

As we can see here, the hydrator used the public API (here getFoo) to retrieve the value.

However, if we use it by reference:

```php
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

$hydrator = new DoctrineHydrator($objectManager, false);
$object   = new SimpleEntity();
$object->setFoo('bar');

$data = $hydrator->extract($object);

echo $data['foo']; // prints 'bar'
```

It now only prints "bar", which shows clearly that the getter has not been called.


### A complete example using Zend\Form

Now that we understand how the hydrator works, let's see how it integrates into the Zend Framework 2's Form component.
We are going to use a simple example with, once again, a BlogPost and a Tag entities. We will see how we can create the
blog post, and being able to edit it.

#### The entities

First, let's define the (simplified) entities, beginning with the BlogPost entity:

```php

namespace Application\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class BlogPost
{
	/**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="Application\Entity\Tag", mappedBy="blogPost", cascade={"persist"})
     */
	protected $tags;


	/**
	 * Never forget to initialize all your collections !
	 */
	public function __construct()
	{
		$this->tags = new ArrayCollection();
	}

	/**
	 * @return integer
	 */
    public function getId()
    {
   		return $this->id;
    }

	/**
	 * @param Collection $tags
	 */
	public function addTags(Collection $tags)
	{
		foreach ($tags as $tag) {
			$tag->setBlogPost($this);
			$this->tags->add($tag);
		}
	}

	/**
	 * @param Collection $tags
	 */
	public function removeTags(Collection $tags)
	{
		foreach ($tags as $tag) {
			$tag->setBlogPost(null);
			$this->tags->removeElement($tag);
		}
	}

	/**
	 * @return Collection
	 */
    public function getTags()
    {
    	return $this->tags;
    }
}
```

And then the Tag entity:

```php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Tag
{
	/**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\BlogPost", inversedBy="tags")
     */
	protected $blogPost;

	/**
	 * @ORM\Column(type="string")
	 */
	protected $name;


	/**
	 * Get the id

	 * @return int
	 */
    public function getId()
    {
   		return $this->id;
    }

	/**
	 * Allow null to remove association
	 *
	 * @param BlogPost $blogPost
	 */
	public function setBlogPost(BlogPost $blogPost = null)
	{
		$this->blogPost = $blogPost;
	}

	/**
	 * @return BlogPost
	 */
    public function getBlogPost()
    {
    	return $this->blogPost;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
    	$this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
    	return $this->name;
    }
}
```

#### The fieldsets

We now need to create two fieldsets that will map those entities. With Zend Framework 2, it's a good practice to create
one fieldset per entity in order to reuse them across many forms.

Here is the fieldset for the Tag. Notice that in this example, I added a hidden input whose name is "id". This is
needed for editing. Most of the time, when you create the Blog Post for the first time, the tags does not exist.
Therefore, the id will be empty. However, when you edit the blog post, all the tags already exists in database (they
have been persisted and have an id), and hence the hidden "id" input will have a value. This allow you to modify a tag
name by modifying an existing Tag entity without creating a new tag (and removing the old one).

```php

namespace Application\Form;

use Application\Entity\Tag;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;

class TagFieldset extends Fieldset implements InputFilterProviderInterface
{
    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct('tag');

        $this->setHydrator(new DoctrineHydrator($objectManager))
             ->setObject(new Tag());

		$this->add(array(
			'type' => 'Zend\Form\Element\Hidden',
			'name' => 'id'
		));

        $this->add(array(
            'type'    => 'Zend\Form\Element\Text',
            'name'    => 'name',
            'options' => array(
                'label' => 'Tag'
            )
        ));
    }

    public function getInputFilterSpecification()
    {
        return array(
            'id' => array(
            	'required' => false
            ),

            'name' => array(
                'required' => true
            )
        );
    }
}
```

And the BlogPost fieldset:

```php

namespace Application\Form;

use Application\Entity\BlogPost;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;

class BlogPostFieldset extends Fieldset implements InputFilterProviderInterface
{
    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct('blog-post');

        $this->setHydrator(new DoctrineHydrator($objectManager))
             ->setObject(new BlogPost());

		$this->add(array(
			'type' => 'Zend\Form\Element\Text',
			'name' => 'title'
		));

		$tagFieldset = new TagFieldset($objectManager);
        $this->add(array(
            'type'    => 'Zend\Form\Element\Collection',
            'name'    => 'tags',
            'options' => array(
            	'count'           => 2,
                'target_element' => $tagFieldset
            )
        ));
    }

    public function getInputFilterSpecification()
    {
        return array(
            'title' => array(
            	'required' => true
            ),
        );
    }
}
```

Plain and easy. The blog post is just a simple fieldset with an element type of type ``Zend\Form\Element\Collection``
that represents the ManyToOne association.

#### The form

Now that we have created our fieldset, we will create two forms: one form for creation and one form for updating.
The form task is to make the glue between the fieldsets. In this simple example, both forms are exactly the same,
but in a real application, you may want to change this behaviour by changing the validation group (for instance, you
may want to disallow the user to modify the title of the blog post when updating).

Here is the create form:

```php
namespace Application\Form;

use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Form\Form;

class CreateBlogPostForm extends Form
{
    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct('create-blog-post-form');

		// The form will hydrate an object of type "BlogPost"
        $this->setHydrator(new DoctrineHydrator($objectManager));

        // Add the user fieldset, and set it as the base fieldset
        $blogPostFieldset = new BlogPostFieldset($objectManager);
        $blogPostFieldset->setUseAsBaseFieldset(true);
        $this->add($blogPostFieldset);

        // … add CSRF and submit elements …

        // Optionally set your validation group here
    }
}
```

And the update form:

```php
namespace Application\Form;

use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Form\Form;

class UpdateBlogPostForm extends Form
{
    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct('update-blog-post-form');

		// The form will hydrate an object of type "BlogPost"
        $this->setHydrator(new DoctrineHydrator($objectManager));

        // Add the user fieldset, and set it as the base fieldset
        $blogPostFieldset = new BlogPostFieldset($objectManager);
        $blogPostFieldset->setUseAsBaseFieldset(true);
        $this->add($blogPostFieldset);

        // … add CSRF and submit elements …

        // Optionally set your validation group here
    }
}
```

#### The controllers

We now have everything. Let's create the controllers.

##### Creation

If the createAction, we will create a new BlogPost and all the associated tags. As a consequence, the hidden ids
for the tags will by empty (because they have not been persisted yet).

Here is the action for create a new blog post:

```php

public function createAction()
{
    // Get your ObjectManager from the ServiceManager
    $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

	// Create the form and inject the ObjectManager
	$form = new CreateBlogPostForm($objectManager);

	// Create a new, empty entity and bind it to the form
	$blogPost = new BlogPost();
	$form->bind($blogPost);

	if ($this->request->isPost()) {
		$form->setData($this->request->getPost());

		if ($form->isValid()) {
			$objectManager->persist($blogPost);
			$objectManager->flush();
		}
	}

	return array('form' => $form);
}
```

The update form is similar, instead that we get the blog post from database instead of creating an empty one:

```php

public function editAction()
{
    // Get your ObjectManager from the ServiceManager
    $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

	// Create the form and inject the ObjectManager
	$form = new UpdateBlogPostForm($objectManager);

	// Create a new, empty entity and bind it to the form
	$blogPost = $this->userService->get($this->params('blogPost_id'));
	$form->bind($blogPost);

	if ($this->request->isPost()) {
		$form->setData($this->request->getPost());

		if ($form->isValid()) {
		    // Save the changes
		    $objectManager->flush();
		}
	}

	return array('form' => $form);
}
```



### Performance considerations

Although using the hydrator is like magical as it abstracts most of the tedious task, you have to be aware that it can
leads to performance issues in some situations. Please carefully read the following paragraphs in order to know how
to solve (and avoid !) them.

#### Unwanting side-effect

You have to be very careful when you are using DoctrineModule hydrator with complex entities that contain a lot of
associations, as a lot of unnecessary calls to database can be made if you are not perfectly aware of what happen
under the hood. To explain this problem, let's have an example.

Imagine the following entity :


```php
namespace Application\Entity;

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

This simple entity contains an id, a string property, and a OneToOne relationship. If you are using Zend Framework 2
forms the correct way, you will likely have a fieldset for every entity, so that you have a perfect mapping between
entities and fieldsets. Here are fieldsets for User and and City entities.

> If you are not comfortable with Fieldsets and how they should work, please refer to [this part of Zend Framework 2
documentation](http://framework.zend.com/manual/2.0/en/modules/zend.form.collections.html).

First the User fieldset :

```php
namespace Application\Form;

use Application\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;

class UserFieldset extends Fieldset implements InputFilterProviderInterface
{
	public function __construct(ObjectManager $objectManager)
	{
		parent::__construct('user');

		$this->setHydrator(new DoctrineHydrator($objectManager))
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

       	$cityFieldset = new CityFieldset($objectManager);
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
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;

class CityFieldset extends Fieldset implements InputFilterProviderInterface
{
	public function __construct(ObjectManager $objectManager)
	{
		parent::__construct('city');

		$this->setHydrator(new DoctrineHydrator($objectManager))
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

Now, let's say that we have one form where a logged user can only change his name. This specific form does not allow
the user to change this city, and the fields of the city are not even rendered in the form. Naively, this form would
be like this :

```php
namespace Application\Form;

use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Form\Form;

class EditNameForm extends Form
{
	public function __construct(ObjectManager $objectManager)
	{
		parent::__construct('edit-name-form');

		$this->setHydrator(new DoctrineHydrator($objectManager));

		// Add the user fieldset, and set it as the base fieldset
		$userFieldset = new UserFieldset($objectManager);
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

Here, we create a simple form called "EditSimpleForm". Because we set the validation group, all the inputs related
to city (postCode and name of the city) won't be validated, which is exactly what we want. The action will look
something like this :

```php
public function editNameAction()
{
    // Get your ObjectManager from the ServiceManager
    $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

	// Create the form and inject the ObjectManager
	$form = new EditNameForm($objectManager);

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

This looks good, isn't it ? However, if we check the queries that are made (for instance using the awesome
[ZendDeveloperTools module](https://github.com/zendframework/ZendDeveloperTools)), we will see that a request is
made to fetch data for the City relationship of the user, and we hence have a completely useless database call,
as this information is not rendered by the form.

You could ask, why ? Yes, we set the validation group, BUT the problem happens during the extracting phase. Here is
how it works : when an object is bound to the form, this latter iterates through all its fields, and tries to extract
the data from the object that is bound. In our example, here is how it work :

1. It first arrives to the UserFieldset. The input are "name" (which is string field), and a "city" which is another fieldset (in our User entity, this is a OneToOne relationship to another entity). The hydrator will extract both the name and the city (which will be a Doctrine 2 Proxy object).
2. Because the UserFieldset contains a reference to another Fieldset (in our case, a CityFieldset), it will, in turn, tries to extract the values of the City to populate the values of the CityFieldset. And here is the problem : City is a Proxy, and hence because the hydrator tries to extract its values (the name and postcode field), Doctrine will automatically fetch the object from the database in order to please the hydrator.

This is absolutely normal, this is how ZF 2 forms work and what make them nearly magic, but in this specific case, it
can leads to disastrous consequences. When you have very complex entities with a lot of OneToMany collections, imagine
how many unnecessary calls can be made (actually, after discovering this problem, I've realized that my applications was
doing 10 unnecessary database calls).

In fact, the fix is ultra simple : if you don't need specific fieldsets in a form, remove them. Here is the fix
EditUserForm :

```php
namespace Application\Form;

use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Form\Form;

class EditNameForm extends Form
{
	public function __construct(ObjectManager $objectManager)
	{
		parent::__construct('edit-name-form');

		$this->setHydrator(new DoctrineHydrator($objectManager));

		// Add the user fieldset, and set it as the base fieldset
		$userFieldset = new UserFieldset($objectManager);
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
