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
use DoctrineModule\Form\Element\Proxy;
use DoctrineModuleTest\Form\Element\TestAsset\FormObject;
use PHPUnit_Framework_TestCase;

/**
 * Tests for the Collection pagination adapter
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Kyle Spraggs <theman@spiffyjr.me>
 */
class ProxyTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Doctrine\Common\Persistence\Mapping\ClassMetadata
     */
    protected $metadata;

    /**
     * @var \DoctrineModule\Form\Element\Proxy
     */
    protected $proxy;

    /**
     * {@inheritDoc}.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->proxy = new Proxy;
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage No object manager was set
     */
    public function testExceptionThrownForMissingObjectManager()
    {
        $this->proxy->setOptions(array('target_class' => 'DoctrineModuleTest\Form\Element\TestAsset\FormObject'));
        $this->proxy->getValueOptions();
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage No target class was set
     */
    public function testExceptionThrownForMissingTargetClass()
    {
        $this->proxy->setOptions(
            array(
                'object_manager' => $this->getMock('Doctrine\Common\Persistence\ObjectManager'),
            )
        );
        $this->proxy->getValueOptions();
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage No method name was set
     */
    public function testExceptionThrownForMissingFindMethodName()
    {
        $objectClass = 'DoctrineModuleTest\Form\Element\TestAsset\FormObject';
        $metadata    = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');

        $objectManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $objectManager->expects($this->once())
            ->method('getClassMetadata')
            ->with($this->equalTo($objectClass))
            ->will($this->returnValue($metadata));

        $this->proxy->setOptions(
            array(
                'object_manager' => $objectManager,
                'target_class'   => $objectClass,
                'find_method'    => array('no_name')
            )
        );

        $this->proxy->getValueOptions();
    }

    public function testExceptionFindMethodNameNotExistentInRepository()
    {
        $objectClass = 'DoctrineModuleTest\Form\Element\TestAsset\FormObject';
        $metadata    = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');

        $objectRepository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');

        $objectManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $objectManager->expects($this->once())
            ->method('getClassMetadata')
            ->with($this->equalTo($objectClass))
            ->will($this->returnValue($metadata));

        $objectManager->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo($objectClass))
            ->will($this->returnValue($objectRepository));

        $this->proxy->setOptions(
            array(
                'object_manager' => $objectManager,
                'target_class'   => $objectClass,
                'find_method'    => array('name' => 'NotExistent'),
            )
        );

        $this->setExpectedException(
            'RuntimeException',
            'Method "NotExistent" could not be found in repository "' . get_class($objectRepository).'"'
        );

        $this->proxy->getValueOptions();
    }

    public function testToStringIsUsedForGetValueOptions()
    {
        $this->prepareProxy();

        $result = $this->proxy->getValueOptions();
        $this->assertEquals($result[0]['label'], 'object one username');
        $this->assertEquals($result[1]['label'], 'object two username');
        $this->assertEquals($result[0]['value'], 1);
        $this->assertEquals($result[1]['value'], 2);
    }

    public function testPropertyGetterUsedForGetValueOptions()
    {
        $this->prepareProxy();

        $this->proxy->setOptions(array('property' => 'password'));

        $this->metadata->expects($this->exactly(2))
            ->method('hasField')
            ->with($this->equalTo('password'))
            ->will($this->returnValue(true));

        $result = $this->proxy->getValueOptions();
        $this->assertEquals($result[0]['label'], 'object one password');
        $this->assertEquals($result[1]['label'], 'object two password');
        $this->assertEquals($result[0]['value'], 1);
        $this->assertEquals($result[1]['value'], 2);
    }

    public function testPublicPropertyUsedForGetValueOptions()
    {
        $this->prepareProxy();

        $this->proxy->setOptions(array('property' => 'email'));

        $this
            ->metadata
            ->expects($this->exactly(2))
            ->method('hasField')
            ->with($this->equalTo('email'))
            ->will($this->returnValue(true));

        $result = $this->proxy->getValueOptions();
        $this->assertEquals($result[0]['label'], 'object one email');
        $this->assertEquals($result[1]['label'], 'object two email');
        $this->assertEquals($result[0]['value'], 1);
        $this->assertEquals($result[1]['value'], 2);
    }

    public function testIsMethodOptionUsedForGetValueOptions()
    {
        $this->prepareProxy();

        $this->proxy->setOptions(
            array(
                'property'  => 'name',
                'is_method' => true,
            )
        );

        $this->metadata->expects($this->never())
            ->method('hasField');

        $result = $this->proxy->getValueOptions();
        $this->assertEquals($result[0]['label'], 'object one firstname object one surname');
        $this->assertEquals($result[1]['label'], 'object two firstname object two surname');
        $this->assertEquals($result[0]['value'], 1);
        $this->assertEquals($result[1]['value'], 2);
    }

    public function testDisplayEmptyItemAndEmptyItemLabelOptionsUsedForGetValueOptions()
    {
        $this->prepareProxy();

        $this->proxy->setOptions(
            array(
                'display_empty_item' => true,
                'empty_item_label'   => '---',
            )
        );

        $result = $this->proxy->getValueOptions();
        $this->assertArrayHasKey('', $result);
        $this->assertEquals($result[''], '---');
    }

    public function testLabelGeneratorUsedForGetValueOptions()
    {
        $this->prepareProxy();

        $this->proxy->setOptions(
            array(
                'label_generator' => function ($targetEntity) {
                    return $targetEntity->getEmail();
                }
            )
        );

        $this->metadata->expects($this->never())
            ->method('hasField');

        $result = $this->proxy->getvalueOptions();
        $this->assertEquals($result[0]['label'], 'object one email');
        $this->assertEquals($result[1]['label'], 'object two email');
        $this->assertEquals($result[0]['value'], 1);
        $this->assertEquals($result[1]['value'], 2);
    }

    public function testExceptionThrownForNonCallableLabelGenerator()
    {
        $this->prepareProxy();

        $this->setExpectedException(
            'InvalidArgumentException',
            'Property "label_generator" needs to be a callable function or a \Closure'
        );

        $this->proxy->setOptions(array('label_generator' => 'I throw an InvalidArgumentException'));
    }

    public function testCanWorkWithEmptyTables()
    {
        $this->prepareEmptyProxy();

        $result = $this->proxy->getValueOptions();
        $this->assertEquals(array(), $result);
    }

    public function testUsingFindMethod()
    {
        $this->prepareFilteredProxy();

        $this->proxy->getValueOptions();
    }

    protected function prepareProxy()
    {
        $objectClass = 'DoctrineModuleTest\Form\Element\TestAsset\FormObject';
        $objectOne   = new FormObject;
        $objectTwo   = new FormObject;

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

        $result = new ArrayCollection(array($objectOne, $objectTwo));

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

        $this->proxy->setOptions(
            array(
                'object_manager' => $objectManager,
                'target_class'   => $objectClass
            )
        );

        $this->metadata = $metadata;
    }

    protected function prepareFilteredProxy()
    {
        $objectClass = 'DoctrineModuleTest\Form\Element\TestAsset\FormObject';
        $objectOne   = new FormObject;
        $objectTwo   = new FormObject;

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

        $result = new ArrayCollection(array($objectOne, $objectTwo));

        $metadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $metadata
            ->expects($this->exactly(2))
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
        $objectRepository
            ->expects($this->once())
            ->method('findBy')
            ->will($this->returnValue($result));

        $objectManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $objectManager
            ->expects($this->once())
            ->method('getClassMetadata')
            ->with($this->equalTo($objectClass))
            ->will($this->returnValue($metadata));

        $objectManager
            ->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo($objectClass))
            ->will($this->returnValue($objectRepository));

        $this->proxy->setOptions(
            array(
                'object_manager' => $objectManager,
                'target_class'   => $objectClass,
                'find_method' => array(
                    'name' => 'findBy',
                    'params' => array(
                        'criteria' => array('email' => 'object one email'),
                    ),
                ),
            )
        );

        $this->metadata = $metadata;
    }

    public function prepareEmptyProxy()
    {
        $objectClass      = 'DoctrineModuleTest\Form\Element\TestAsset\FormObject';
        $result           = new ArrayCollection();
        $metadata         = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $objectRepository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');

        $objectRepository
            ->expects($this->once())
            ->method('findAll')
            ->will($this->returnValue($result));

        $objectManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $objectManager
            ->expects($this->once())
            ->method('getClassMetadata')
            ->with($this->equalTo($objectClass))
            ->will($this->returnValue($metadata));

        $objectManager
            ->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo($objectClass))
            ->will($this->returnValue($objectRepository));

        $this->proxy->setOptions(
            array(
                'object_manager' => $objectManager,
                'target_class'   => $objectClass
            )
        );

        $this->metadata = $metadata;
    }
}
