Form Elements
-------------

DoctrineModule comes with functionality that can automatically fill the
`ValueOptions` of Select, MultiCheckbox or Radio Form Elements with data from a
`ObjectRepository`.

### Usage

Add a `DoctrineModule\Form\Element\ObjectSelect`,
`DoctrineModule\Form\Element\ObjectRadio` or
`DoctrineModule\Form\Element\ObjectMultiCheckbox` to your Form. For this to
work, you need to specify at least an `object_manager`, the `target_class` to
use and a `property` of the class to use as the Label.

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
        $this->add([
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'name',
            'options' => [
                'object_manager' => $this->getObjectManager(),
                'target_class'   => 'Module\Entity\SomeEntity',
                'property'       => 'property',
            ],
        ]);
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

In times you want to change the display of the label you will need to use the
`label_generator` option. This option allows you to modify the label as much as
you like. In this simple example i will concatenate two properties with a dash.

```php
$this->add([
    'type' => 'DoctrineModule\Form\Element\ObjectSelect',
    'name' => 'name',
    'options' => [
        'object_manager'  => $this->getObjectManager(),
        'target_class'    => 'Module\Entity\SomeEntity',
        'label_generator' => function ($targetEntity) {
            return $targetEntity->getId() . ' - ' . $targetEntity->getTitle();
        },
    ],
]);
```

The callable function will always receive the target entity as a parameter so
you will be able to use all functionalities your entities provide. Another
example would be to completely switch out the labels in case your website has
specific options to provide more accessible labels.

```php
$this->add([
    'type' => 'DoctrineModule\Form\Element\ObjectSelect',
    'name' => 'name',
    'options' => [
        'object_manager'  => $this->getObjectManager(),
        'target_class'    => 'Module\Entity\SomeEntity',
        'label_generator' => function ($targetEntity) use ($someSession) {
            if ('accessible' === $someSession->getCurrentMode()) {
                return $targetEntity->getAccessibleLabel();
            }

            return $targetEntity->getLabel();
        },
    ],
]);
```

### Example 3 : extended version

If you don't need or want the entire repository you can specify a `find_method`
to use. This method must exist in the repository. The following example executes
the `findBy` method and passes in the specified parameters, but when using
custom repositories you can do even more advanced queries! Also you can specify
a method as a property by setting `is_method` to true.

```php
$this->add([
    'type' => 'DoctrineModule\Form\Element\ObjectSelect',
    'name' => 'name',
    'options' => [
        'object_manager' => $this->getObjectManager(),
        'target_class'   => 'Module\Entity\User',
        'property'       => 'ComposedOfSeveralProperties',
        'is_method'      => true,
        'find_method'    => [
            'name'   => 'findBy',
            'params' => [
                'criteria' => ['active' => 1],

                // Use key 'orderBy' if using ORM
                'orderBy'  => ['lastname' => 'ASC'],

                // Use key 'sort' if using ODM
                'sort'  => ['lastname' => 'ASC'],
            ],
        ],
    ],
]);
```

### Example 4 : including an empty option

If you want to include an empty option at the top, set the `display_empty_item`
setting to true. You can also specify the `empty_item_label` setting, the
default is an empty string.

```php
$this->add([
    'type' => 'DoctrineModule\Form\Element\ObjectSelect',
    'name' => 'name',
    'options' => [
        'object_manager'     => $this->getObjectManager(),
        'target_class'       => 'Module\Entity\SomeEntity',
        'property'           => 'property',
        'display_empty_item' => true,
        'empty_item_label'   => '---',
    ],
]);
```

### Example 5 : Add html attributes to the <option> elements

To set custom HTML attributes on each `valueOption` you can use the `option_attributes` setting to specify an array of
key/value pairs whereby the keys represent a valid HTML attribute (data-*, aria-*, onEvent, etc.).

The value needs to be of type `string` or `callable` (in which case a `string` - or something able to be casted to
string - needs to be returned). Check the following example:

```php
$this->add([
    'type' => 'DoctrineModule\Form\Element\ObjectSelect',
    'name' => 'test',
    'options' => [
        'object_manager'    => $this->getObjectManager(),
        'target_class'      => 'Module\Entity\SomeEntity',
        'property'          => 'property',
        'option_attributes' => [
            'class'   => 'styledOption',
            'data-id' => function (\Module\Entity\SomeEntity $entity) {
                return $entity->getId();
            },
        ],
    ],
]);
```

The above example will generate HTML options with a data-key attribute:

```html
<select name="test">
    <option value="1" class="styledOption" data-id="1">property one</option>
    <option value="2" class="styledOption" data-id="2">property two</option>
</select>
```

It is noteworthy that, when working with an option_attribute value of type `callable`, you do **not** need to define
the fully qualified classname into the function. The object passed into the function will always be identical to
the type you define on the key `target_class`.

### Example 6: Implementing <optgroup> support

Once lists become larger there's a big user-experience bonus when lists are groupt using the html <optgroup> attribute.
DoctrineModule provides this functionality with the `optgroup_identifier`.

The assumption DoctrineModule does however is that your data structure has the optgroup-grouping in mind. See the
following example:

**Add the Select list like this:**

```php
$this->add([
    'type' => 'DoctrineModule\Form\Element\ObjectSelect',
    'name' => 'name',
    'options' => [
        'object_manager'      => $this->getObjectManager(),
        'target_class'        => 'Module\Entity\SomeEntity',
        'property'            => 'property',
        'optgroup_identifier' => 'category',
    ],
]);
```

**With your data structure like this:**

```
id  | property   | category
1   | Football   | sports
2   | Basketball | sports
3   | Spaghetti  | food
```

**Will create a HTML Select list like this:**

```html
<select name="name">
    <optgroup label="sports">
        <option value="1">Football</option>
        <option value="2">Basketball</option>
    </optgroup>
    <optgroup label="food">
        <option value="3">Spaghetti</option>
    </optgroup>
</select>
```

### Example 7: <optgroup> formatting on empty optgroups

In case you define an `optgroup_identifier` and the data inside this column is empty or `null` you have two options of
rendering these cases. From a UX point of view you should group all "loose" entries inside a group that you call
"others" or the likes of that. But you're also able to render them without any grouping at all. Here's both examples:

#### 7.1: Rendering without a default group

To render without a default group you have to change nothing. This is the default behavior

**Add the Select list like this:**

```php
$this->add([
    'type' => 'DoctrineModule\Form\Element\ObjectSelect',
    'name' => 'name',
    'options' => [
        'object_manager'      => $this->getObjectManager(),
        'target_class'        => 'Module\Entity\SomeEntity',
        'property'            => 'property',
        'optgroup_identifier' => 'category',
    ],
]);
```

**With your data structure like this:**

```
id  | property   | category
1   | Football   | sports
2   | Basketball |
3   | Spaghetti  | food
```

**Will create a HTML Select list like this:**

```html
<select name="name">
    <optgroup label="sports">
        <option value="1">Football</option>
    </optgroup>
    <optgroup label="food">
        <option value="3">Spaghetti</option>
    </optgroup>
    <option value="2">Basketball</option>
</select>
```

Notice how the value for "Basketball" has not been wrapped with an `<optgroup>` element.

#### 7.2: Rendering with a default group

To group all loose values into a unified group, simply add the `optgroup_default` parameter to the options.

**Add the Select list like this:**

```php
$this->add([
    'type' => 'DoctrineModule\Form\Element\ObjectSelect',
    'name' => 'name',
    'options' => [
        'object_manager'      => $this->getObjectManager(),
        'target_class'        => 'Module\Entity\SomeEntity',
        'property'            => 'property',
        'optgroup_identifier' => 'category',
        'optgroup_default'    => 'Others',
    ],
]);
```

**With your data structure like this:**

```
id  | property   | category
1   | Football   | sports
2   | Basketball |
3   | Spaghetti  | food
```

**Will create a HTML Select list like this:**

```html
<select name="name">
    <optgroup label="sports">
        <option value="1">Football</option>
    </optgroup>
    <optgroup label="others">
        <option value="2">Basketball</option>
    </optgroup>
    <optgroup label="food">
        <option value="3">Spaghetti</option>
    </optgroup>
</select>
```
