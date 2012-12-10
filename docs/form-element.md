## Form Elements

DoctrineModule comes with implementations of Form Elements typically used for 'selecting' doctrine objects. It can automatically fill the `ValueOptions` of a MultiCheckbox, Radio or Select element for you.

### Usage

Add a `DoctrineModule\Form\Element\ObjectSelect`, `DoctrineModule\Form\Element\ObjectRadio` or `DoctrineModule\Form\Element\ObjectMultiCheckbox` to your Form.
For this to work, you need to pass in at least a `object_manager`, the `target_class` and a `property` of the class to use as the Label.

#### Example 1 : simple example
```php

namespace Module\Form;

use Zend\Form\Form;
use Doctrine\Common\Persistence\ObjectManager;

class MyForm extends Form
{
	protected $objectManager;
	
    public function __construct()
    {
    	parent::__construct('MyForm');
    }
    
    public function init()
    {
    	$this->add(
    		'type' => 'DoctrineModule\Form\Element\ObjectSelect',
    		'name' => 'name',
    		'options' => array(
    			'object_manager' => $this->getObjectManager(),
    			'target_class' 	 => 'Module\Entity\SomeEntity',
    			'property'		 => 'property',
    		),
    	);
    }
    
    public function setObjectManager(ObjectManager $objectManager)
    {
    	$this->objectManager = $objectManager;
    }
    
    public function getObjectManager()
    {
    	return $this->objectManager;
    }    
}
```

When to Form gets displayed the `findAll` method will be executed in the `ObjectRepository`. See the next example if you don't need the entire repository.

> Please note that you cannot create this field inside the constructor, since the ObjectManager is needed. First make sure you set a ObjectManager and then `init` the Form.

### Example 2 : extended version

If you don't need or want the entire repository you can specify a `find_method` to use. This method must exist in the repository. The following example executes the `findBy` method and passes in the specified parameters, but when using custom repositories you can do even more advanced queries!
Also you can specify the property as being a method by setting `is_method` to true.

```php
$this->add(
	'type' => 'DoctrineModule\Form\Element\ObjectSelect',
	'name' => 'name',
	'options' => array(
		'object_manager' => $this->getObjectManager(),
		'target_class' 	 => 'Module\Entity\User',
		'property'		 => 'ComposedOfSeveralProperties',
		'is_method'	     => true,
		'find_method'	 => array(
			'name' 	 => 'findBy',
			'params' => array(
				'criteria' => array('active' => 1),
				'orderBy'  => array('lastname' => 'ASC'),
			),
		),
	),
);
```