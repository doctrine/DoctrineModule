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
use DoctrineModule\Validator\NoObjectExists;

/**
 * Tests for the NoObjectExists tests
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class NoObjectExistsTest extends BaseTestCase
{
    public function testCanValidateWithNoAvailableObjectInRepository()
    {
        $repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');

        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->will($this->returnValue(null));

        $validator = new NoObjectExists(array('object_repository' => $repository, 'fields' => 'matchKey'));

        $this->assertTrue($validator->isValid('matchValue'));
    }

    public function testCannotValidateWithAvailableObjectInRepository()
    {
        $repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');

        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->will($this->returnValue(new stdClass()));

        $validator = new NoObjectExists(array('object_repository' => $repository, 'fields' => 'matchKey'));

        $this->assertFalse($validator->isValid('matchValue'));
    }
    
    public function testErrorMessageIsStringInsteadArray()
    {
        $repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->will($this->returnValue(new stdClass()));
        $validator = new NoObjectExists(array('object_repository' => $repository, 'fields' => 'matchKey'));
        
        $this->assertFalse($validator->isValid('matchValue'));

        $messageTemplates = $validator->getMessageTemplates();
        
        $expectedMessage = str_replace(
            '%value%',
            'matchValue',
            $messageTemplates[NoObjectExists::ERROR_OBJECT_FOUND]
        );
        $messages        = $validator->getMessages();
        $receivedMessage = $messages[NoObjectExists::ERROR_OBJECT_FOUND];

        $this->assertTrue($expectedMessage == $receivedMessage);
    }
}
