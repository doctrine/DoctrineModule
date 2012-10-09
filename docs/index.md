# DoctrineModule

DoctrineModule provides a bridge between Zend Framework 2 and Doctrine 2. 
It gives you access to features that can be used across Doctrine 2 ORM as well as Doctrine 2 ODM.
It provides an abstraction layer on top of [`Doctrine\Common`](https://github.com/doctrine/common)
which allows the end user to build functionality being completely unaware if he's currently working
with Doctrine ORM or Doctrine MongoDB ODM.

To use Doctrine ORM or ODM, you will need [DoctrineORMModule](https://github.com/doctrine/DoctrineORMModule) 
or [DoctrineMongoODMModule](https://github.com/doctrine/DoctrineMongoODMModule) respectively.

You can find more details about the features offered by DoctrineModule:

* [Authentication documentation](https://github.com/doctrine/DoctrineModule/blob/master/docs/authentication.md): this explains how you can use the DoctrineModule authentication adapter and authentication storage adapter to provide a simple way to authenticate users using Doctrine.
* [Caching documentation](https://github.com/doctrine/DoctrineModule/blob/master/docs/caching.md): DoctrineModule provides simple classes to allow easier caching using Doctrine.
* [CLI documentation](https://github.com/doctrine/DoctrineModule/blob/master/docs/cli.md): learn how to use the Doctrine 2 command line tool, and how to add your own command.
* [Hydrator documentation](https://github.com/doctrine/DoctrineModule/blob/master/docs/hydrator.md): if you are using Zend Framework 2 Forms (and I hope you are !), DoctrineModule hydrator provides a powerful hydrator that allow you to easily deal with OneToOne, OneToMany and ManyToOne relationships when using forms.
* [Paginator documentation](https://github.com/doctrine/DoctrineModule/blob/master/docs/paginator.md): discover how to use the DoctrineModule Paginator adapter.
* [Validator documentation](https://github.com/doctrine/DoctrineModule/blob/master/docs/validator.md): this chapter explains how to use ObjectExists and NoObjectExists validator, that allow you to easily validate if a given entity exists or not.