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

namespace DoctrineModuleTest\Validator\Adapter;

use stdClass;
use PHPUnit_Framework_TestCase as BaseTestCase;
use DoctrineModule\Validator\UniqueObject;

/**
 * Tests for the UniqueObject validator
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Oskar Bley <oskar@programming-php.net>
 */
class UniqueObjectTest extends BaseTestCase
{
    public function testCanValidateWithNotAvailableObjectInRepository()
    {
        $repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(array('matchKey' => 'matchValue'))
            ->will($this->returnValue(null));

        $validator = new UniqueObject(
            array(
                'object_repository' => $repository,
                'fields'            => 'matchKey',
            )
        );
        $this->assertTrue($validator->isValid('matchValue'));
    }

    public function testCanValidateIfThereIsTheSameObjectInTheRepository()
    {
        $match = new stdClass();
        $match->id = 'identifier';

        $repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(array('matchKey' => 'matchValue'))
            ->will($this->returnValue($match));

        $validator = new UniqueObject(
            array(
                'object_repository' => $repository,
                'fields'            => 'matchKey',
            )
        );
        $this->assertTrue($validator->isValid('matchValue', array('id' => 'identifier')));
    }

    public function testCannotValidateIfThereIsAnotherObjectWithTheSameValueInTheRepository()
    {
        $match = new stdClass();
        $match->id = 'identifier';

        $repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(array('matchKey' => 'matchValue'))
            ->will($this->returnValue($match));

        $validator = new UniqueObject(
            array(
                'object_repository' => $repository,
                'fields'            => 'matchKey',
            )
        );
        $this->assertFalse($validator->isValid('matchValue', array('id' => 'another identifier')));
    }

    public function testCanAccessThePropertiesByGetter()
    {
        $match = $this->getMock('stdClass', array('getId'));
        $match->expects($this->once())
              ->method('getId')
              ->will($this->returnValue('identifier'));

        $repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(array('matchKey' => 'matchValue'))
            ->will($this->returnValue($match));

        $validator = new UniqueObject(
            array(
                'object_repository' => $repository,
                'fields'            => 'matchKey',
            )
        );
        $this->assertFalse($validator->isValid('matchValue', array('id' => 'another identifier')));
    }

    /**
     * @expectedException Zend\Validator\Exception\RuntimeException
     * @expectedExceptionMessage Property (id) in (stdClass) is not accessible. You should implement stdClass::getId()
     */
    public function testThrowsAnExceptionWhenAnIdentifierPropertyDoesNotExistInTheMatchedObject()
    {
        $match = new stdClass();

        $repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(array('matchKey' => 'matchValue'))
            ->will($this->returnValue($match));

        $validator = new UniqueObject(
            array(
                'object_repository' => $repository,
                'fields'            => 'matchKey',
            )
        );
        $validator->isValid('matchValue', array('id' => 'identifier'));
    }

    /**
     * @expectedException Zend\Validator\Exception\RuntimeException
     * @expectedExceptionMessage Expected context to be an array but is null
     */
    public function testThrowsAnExceptionOnMissingContext()
    {
        $match = new stdClass();
        $match->id = 'identifier';

        $repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(array('matchKey' => 'matchValue'))
            ->will($this->returnValue($match));

        $validator = new UniqueObject(
            array(
                'object_repository' => $repository,
                'fields'            => 'matchKey',
            )
        );
        $validator->isValid('matchValue');
    }

    /**
     * @expectedException Zend\Validator\Exception\RuntimeException
     * @expectedExceptionMessage Expected context to contain id
     */
    public function testThrowsAnExceptionOnMissingIdentifierInContext()
    {
        $match = new stdClass();
        $match->id = 'identifier';

        $repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(array('matchKey' => 'matchValue'))
            ->will($this->returnValue($match));

        $validator = new UniqueObject(
            array(
                'object_repository' => $repository,
                'fields'            => 'matchKey',
            )
        );
        $validator->isValid('matchValue', array());
    }

    /**
     * @expectedException Zend\Validator\Exception\InvalidArgumentException
     * @expectedExceptionMessage There must be at least one identifier
     */
    public function testThrowsAnExceptionOnMissingIdentifierFields()
    {
        $repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');

        $validator = new UniqueObject(
            array(
                'object_repository' => $repository,
                'fields'            => 'matchKey',
                'id_fields'         => array()
            )
        );
        $validator->isValid('matchValue', array());
    }

    public function testCanHandleMultipleIdentifiers()
    {
        $match = new stdClass();
        $match->id  = 'identifier';
        $match->id2 = 'identifier2';

        $repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(array('matchKey' => 'matchValue'))
            ->will($this->returnValue($match));

        $validator = new UniqueObject(
            array(
                'object_repository' => $repository,
                'fields'            => 'matchKey',
                'id_fields'         => array('id', 'id2')
            )
        );
        $this->assertFalse($validator->isValid('matchValue', array('id' => 'identifier', 'id2' => 'notIdentifier2')));
    }
}
