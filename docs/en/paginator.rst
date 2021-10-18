Paginator
---------

Collection adapter
~~~~~~~~~~~~~~~~~~

DoctrineModule provides a simple Paginator adapter that can be used with
DoctrineCollection.

   Note : if you are using Doctrine 2 ORM, what you are looking for is
   probably a Paginator adapter that can be used with Doctrine 2
   Paginators. Luckily, DoctrineORMModule provides such a paginator
   adapter. You can find the documentation here :

Simple example
^^^^^^^^^^^^^^

Here is how you can use the DoctrineModule paginator adapter :

.. code:: php

   use Doctrine\Common\Collections\ArrayCollection;
   use DoctrineModule\Paginator\Adapter\Collection as CollectionAdapter;
   use Laminas\Paginator\Paginator;

   // Create a Doctrine 2 Collection
   $doctrineCollection = new ArrayCollection(range(1, 101));

   // Create the adapter
   $adapter = new CollectionAdapter($doctrineCollection);

   // Create the paginator itself
   $paginator = new Paginator($adapter);
   $paginator->setCurrentPageNumber(1)
             ->setItemCountPerPage(5);

   // Pass it to the view, and use it like a "standard" Laminas paginator

For more information about Laminas paginator, please read the
`laminas-paginator
documentation <https://docs.laminas.dev/laminas-paginator/>`__.

Selectable adapter
~~~~~~~~~~~~~~~~~~

DoctrineModule also provides another paginator adapter that is based on
new Selectable and Criteria interfaces from Doctrine >= 2.3. It works
with any Selectable objects (ObjectRepository for instance).

.. _simple-example-1:

Simple example
^^^^^^^^^^^^^^

You can use it without any existing Criteria object:

.. code:: php

   use DoctrineModule\Paginator\Adapter\Selectable as SelectableAdapter;
   use Laminas\Paginator\Paginator;

   // Create the adapter
   $adapter = new SelectableAdapter($objectRepository); // An object repository implements Selectable

   // Create the paginator itself
   $paginator = new Paginator($adapter);
   $paginator->setCurrentPageNumber(1)
             ->setItemCountPerPage(5);

   // Pass it to the view, and use it like a "standard" Laminas paginator

If you want to further filter the results, you can optionally pass an
existing Criteria object:

.. code:: php

   use Doctrine\Common\Collections\Criteria as DoctrineCriteria;
   use DoctrineModule\Paginator\Adapter\Selectable as SelectableAdapter;
   use Laminas\Paginator\Paginator;

   // Create the criteria
   $expr     = DoctrineCriteria::expr()->eq('foo', 'bar');
   $criteria = new DoctrineCriteria($expr);

   // Create the adapter
   $adapter = new SelectableAdapter($objectRepository, $criteria); // An object repository implements Selectable

   // Create the paginator itself
   $paginator = new Paginator($adapter);
   $paginator->setCurrentPageNumber(1)
             ->setItemCountPerPage(5);

   // Pass it to the view, and use it like a "standard" Laminas paginator

For more information about Laminas paginator, please read the
`laminas-paginator
documentation <https://docs.laminas.dev/laminas-paginator/>`__.
