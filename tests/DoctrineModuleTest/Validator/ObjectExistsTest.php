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
use DoctrineModule\Validator\ObjectExists;

/**
 * Tests for the ObjectExists validator
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Marco Pivetta <ocramius@gmail.com>
 *
 * @covers \DoctrineModule\Validator\ObjectExists
 */
class ObjectExistsTest extends BaseTestCase
{
    public function testCanValidateWithSingleField()
    {
        $repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');

        $repository
            ->expects($this->exactly(2))
            ->method('findOneBy')
            ->with(array('matchKey' => 'matchValue'))
            ->will($this->returnValue(new stdClass()));

        $validator = new ObjectExists(array('object_repository' => $repository, 'fields' => 'matchKey'));

        $this->assertTrue($validator->isValid('matchValue'));
        $this->assertTrue($validator->isValid(array('matchKey' => 'matchValue')));
    }

    public function testCanValidateWithMultipleFields()
    {
        $repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $repository
            ->expects($this->exactly(2))
            ->method('findOneBy')
            ->with(array('firstMatchKey' => 'firstMatchValue', 'secondMatchKey' => 'secondMatchValue'))
            ->will($this->returnValue(new stdClass()));

        $validator = new ObjectExists(
            array(
                'object_repository' => $repository,
                'fields'            => array(
                    'firstMatchKey',
                    'secondMatchKey',
                ),
            )
        );
        $this->assertTrue(
            $validator->isValid(
                array(
                    'firstMatchKey'  => 'firstMatchValue',
                    'secondMatchKey' => 'secondMatchValue',
                )
            )
        );
        $this->assertTrue($validator->isValid(array('firstMatchValue', 'secondMatchValue')));
    }

    public function testCanValidateFalseOnNoResult()
    {
        $repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->will($this->returnValue(null));

        $validator = new ObjectExists(
            array(
                'object_repository' => $repository,
                'fields'            => 'field',
            )
        );
        $this->assertFalse($validator->isValid('value'));
    }

    public function testWillRefuseMissingRepository()
    {
        $this->setExpectedException('Zend\Validator\Exception\InvalidArgumentException');

        new ObjectExists(array('fields' => 'field'));
    }

    public function testWillRefuseNonObjectRepository()
    {
        $this->setExpectedException('Zend\Validator\Exception\InvalidArgumentException');

        new ObjectExists(array('object_repository' => 'invalid', 'fields' => 'field'));
    }

    public function testWillRefuseInvalidRepository()
    {
        $this->setExpectedException('Zend\Validator\Exception\InvalidArgumentException');

        new ObjectExists(array('object_repository' => new stdClass(), 'fields' => 'field'));
    }

    public function testWillRefuseMissingFields()
    {
        $this->setExpectedException('Zend\Validator\Exception\InvalidArgumentException');

        new ObjectExists(
            array(
                'object_repository' => $this->getMock('Doctrine\Common\Persistence\ObjectRepository'),
            )
        );
    }

    public function testWillRefuseEmptyFields()
    {
        $this->setExpectedException('Zend\Validator\Exception\InvalidArgumentException');

        new ObjectExists(
            array(
                'object_repository' => $this->getMock('Doctrine\Common\Persistence\ObjectRepository'),
                'fields'            => array(),
            )
        );
    }

    public function testWillRefuseNonStringFields()
    {
        $this->setExpectedException('Zend\Validator\Exception\InvalidArgumentException');
        new ObjectExists(
            array(
                'object_repository' => $this->getMock('Doctrine\Common\Persistence\ObjectRepository'),
                'fields'            => array(
                    123
                ),
            )
        );
    }

    public function testWillNotValidateOnFieldsCountMismatch()
    {
        $this->setExpectedException(
            'Zend\Validator\Exception\RuntimeException',
            'Provided values count is 1, while expected number of fields to be matched is 2'
        );
        $validator = new ObjectExists(
            array(
                'object_repository' => $this->getMock('Doctrine\Common\Persistence\ObjectRepository'),
                'fields'            => array('field1', 'field2')
            )
        );
        $validator->isValid(array('field1Value'));
    }

    public function testWillNotValidateOnFieldKeysMismatch()
    {
        $this->setExpectedException(
            'Zend\Validator\Exception\RuntimeException',
            'Field "field2" was not provided, but was expected since the configured field lists needs it for validation'
        );

        $validator = new ObjectExists(
            array(
                'object_repository' => $this->getMock('Doctrine\Common\Persistence\ObjectRepository'),
                'fields'            => array('field1', 'field2')
            )
        );

        $validator->isValid(array('field1' => 'field1Value'));
    }
}
