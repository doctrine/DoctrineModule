## Form Elements

DoctrineModule comes with functionality that can automatically fill the 
`ValueOptions` of Select, MultiCheckbox or Radio Form Elements with data from a 
`ObjectRepository`.

### Usage

Add a `DoctrineModule\Form\Element\ObjectSelect`, 
`DoctrineModule\Form\Element\ObjectRadio` or 
`DoctrineModule\Form\Element\ObjectMultiCheckbox` to your Form.
For this to work, you need to specify at least an `object_manager`, 
the `target_class` to use and a `property` of the class to use as the Label.

#### Example 1 : simple example
```php

namespace Module\Form;

use Zend\Form\Form;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;

class MyForm extends Form implements ObjectManagerAwareInterface
{
    protected $objectManager;
	
    public function init()
    {
        $this->add(
            array(
                'type' => 'DoctrineModule\Form\Element\ObjectSelect',
                'name' => 'name',
                'options' => array(
                    'object_manager' => $this->getObjectManager(),
                    'target_class'   => 'Module\Entity\SomeEntity',
                    'property'       => 'property',
                ),
            )
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

When the Form gets rendered the `findAll` method of the `ObjectRepository` will 
be executed by default.

### Example 2 : extended version

If you don't need or want the entire repository you can specify a `find_method` 
to use. This method must exist in the repository. The following example executes 
the `findBy` method and passes in the specified parameters, but when using 
custom repositories you can do even more advanced queries!
Also you can specify a method as a property by setting `is_method` to true.

```php
$this->add(
    array(
        'type' => 'DoctrineModule\Form\Element\ObjectSelect',
        'name' => 'name',
        'options' => array(
            'object_manager' => $this->getObjectManager(),
            'target_class'   => 'Module\Entity\User',
            'property'       => 'ComposedOfSeveralProperties',
            'is_method'      => true,
            'find_method'    => array(
                'name'   => 'findBy',
                'params' => array(
                    'criteria' => array('active' => 1),
                    'orderBy'  => array('lastname' => 'ASC'),
                ),
            ),
        ),
    )
);
```