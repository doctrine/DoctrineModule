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