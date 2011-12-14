# Installing the SpiffyDoctrine module for Zend Framework 2 
The simplest way to install is to clone the repository into your /vendor directory add the 
SpiffyDoctrine key to your modules array before your Application module key.

  1. cd my/project/folder
  2. git clone git://github.com/SpiffyJr/SpiffyDoctrine.git vendor/SpiffyDoctrine
  3. git submodule update --init --recursive vendor/doctrine-odm
  4. open my/project/folder/configs/application.config.php and add 'SpiffyDoctrine' to your 'modules' parameter.
  5. drop config/module.spiffydoctrine.config.php.dist into your application config/autoload folder
     and make the appropriate changes.
     
## Usage
Access the entity manager using the following locator: 

    $em = $this->getLocator()->get('doctrine_mongo');