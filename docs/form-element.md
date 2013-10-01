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

### Example 2 : modifying the label

In times you want to change the display of the label you will need to use the ```label_generator``` option.
This option allows you to modify the label as much as you like. In this simple example i will concatenate two
properties with a dash.

```php
$this->add(
    array(
        'type' => 'DoctrineModule\Form\Element\ObjectSelect',
        'name' => 'name',
        'options' => array(
            'object_manager'  => $this->getObjectManager(),
            'target_class'    => 'Module\Entity\SomeEntity',
            'label_generator' => function($targetEntity) {
                return $targetEntity->getId() . ' - ' . $targetEntity->getTitle();
            },
        ),
    )
);
```

The callable function will always receive the target entity as a parameter so you will be able to use all
functionalities your entities provide. Another example would be to completely switch out the labels in case
your website has specific options to provide more accessible labels.

```php
$this->add(
    array(
        'type' => 'DoctrineModule\Form\Element\ObjectSelect',
        'name' => 'name',
        'options' => array(
            'object_manager'  => $this->getObjectManager(),
            'target_class'    => 'Module\Entity\SomeEntity',
            'label_generator' => function($targetEntity) use ($someSession) {
                if ('accessible' === $someSession->getCurrentMode()) {
                    return $targetEntity->getAccessibleLabel();
                } else {
                    return $targetEntity->getLabel();
                }
            },
        ),
    )
);
```

### Example 3 : extended version

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

### Example 4 : including an empty option

If you want to include an empty option at the top, set the `include_empty_option` setting to true.
You can also specify the `empty_option_label` setting, the default is an empty string.

```php
$this->add(
    array(
        'type' => 'DoctrineModule\Form\Element\ObjectSelect',
        'name' => 'name',
        'options' => array(
            'object_manager'     => $this->getObjectManager(),
            'target_class'       => 'Module\Entity\SomeEntity',
            'property'           => 'property',
            'display_empty_item' => true,
            'empty_item_label'   => '---',
        ),
    )
);
```