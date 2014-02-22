<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace DoctrineModuleTest\Form\Element;

use Doctrine\Common\Collections\ArrayCollection;
use DoctrineModule\Form\Element\ObjectMultiCheckbox;
use DoctrineModule\Form\Element\ObjectSelect;
use DoctrineModuleTest\Form\Element\TestAsset\FormObject;
use PHPUnit_Framework_TestCase;

/**
 * Tests for the ObjectSelect element
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Kyle Spraggs <theman@spiffyjr.me>
 */

class ObjectSelectTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ArrayCollection
     */
    protected $values;

    /**
     * @var \DoctrineModule\Form\Element\ObjectMultiCheckbox
     */
    protected $element;

    /**
     * {@inheritDoc}.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->element = new ObjectSelect();

        $this->prepareProxy();
    }

    public function testSetValueWithCollection()
    {
        $this->element->setAttribute('multiple', true);

        $this->element->setValue(
            $this->values
        );

        $this->assertEquals(
            array(1, 2),
            $this->element->getValue()
        );
    }

    public function testSetValueWithArray()
    {
        $this->element->setAttribute('multiple', true);

        $this->element->setValue(
            $this->values->toArray()
        );

        $this->assertEquals(
            array(1, 2),
            $this->element->getValue()
        );
    }

    public function testSetValueSingleValue()
    {
        $value = $this->values->toArray();

        $this->element->setValue(
            $value[0]
        );

        $this->assertEquals(
            1,
            $this->element->getValue()
        );
    }

    protected function prepareProxy()
    {
        $objectClass = 'DoctrineModuleTest\Form\Element\TestAsset\FormObject';
        $objectOne   = new FormObject();
        $objectTwo   = new FormObject();

        $objectOne->setId(1)
            ->setUsername('object one username')
            ->setPassword('object one password')
            ->setEmail('object one email')
            ->setFirstname('object one firstname')
            ->setSurname('object one surname');

        $objectTwo->setId(2)
            ->setUsername('object two username')
            ->setPassword('object two password')
            ->setEmail('object two email')
            ->setFirstname('object two firstname')
            ->setSurname('object two surname');

        $this->values = $result = new ArrayCollection(array($objectOne, $objectTwo));

        $metadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $metadata
            ->expects($this->any())
            ->method('getIdentifierValues')
            ->will(
                $this->returnCallback(
                    function () use ($objectOne, $objectTwo) {
                        $input = func_get_args();
                        $input = array_shift($input);

                        if ($input == $objectOne) {
                            return array('id' => 1);
                        } elseif ($input == $objectTwo) {
                            return array('id' => 2);
                        }

                        return array();
                    }
                )
            );

        $objectRepository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $objectRepository->expects($this->any())
            ->method('findAll')
            ->will($this->returnValue($result));

        $objectManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $objectManager->expects($this->any())
            ->method('getClassMetadata')
            ->with($this->equalTo($objectClass))
            ->will($this->returnValue($metadata));

        $objectManager
            ->expects($this->any())
            ->method('getRepository')
            ->with($this->equalTo($objectClass))
            ->will($this->returnValue($objectRepository));

        $this->element->getProxy()->setOptions(
            array(
                'object_manager' => $objectManager,
                'target_class'   => $objectClass
            )
        );

        $this->metadata = $metadata;
    }
}
