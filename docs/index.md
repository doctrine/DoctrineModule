# DoctrineModule

DoctrineModule provides a bridge between Zend Framework 2 and Doctrine 2. 
It gives you access to features that can be used across Doctrine 2 ORM as well as Doctrine 2 ODM.
It provides an abstraction layer on top of [`Doctrine\Common`](https://github.com/doctrine/common)
which allows the end user to build functionality being completely unaware if he's currently working
with Doctrine ORM or Doctrine MongoDB ODM.

To use Doctrine ORM or ODM, you will need [DoctrineORMModule](https://github.com/doctrine/DoctrineORMModule) 
or [DoctrineMongoODMModule](https://github.com/doctrine/DoctrineMongoODMModule) respectively.
