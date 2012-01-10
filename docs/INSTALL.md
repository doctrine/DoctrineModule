# Installing the DoctrineModule for Zend Framework 2 
The simplest way to install is to clone the repository into your /vendor directory add the 
DoctrineModule key to your modules array before your Application module key.

  1. cd my/project/folder
  2. git clone git://github.com/doctrine/DoctrineModule.git vendor/DoctrineModule
  3. open my/project/folder/configs/application.config.php and add 'DoctrineModule' to your 'modules' parameter.