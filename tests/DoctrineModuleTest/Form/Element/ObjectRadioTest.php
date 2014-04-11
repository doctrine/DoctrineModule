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

use DoctrineModule\Form\Element\ObjectRadio;
use PHPUnit_Framework_TestCase as TestCase;

class ObjectRadioTest extends TestCase
{
    /**
     * {@inheritDoc}.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->element = new ObjectRadio();
    }

    public function testGetValueOptionsDoesntCauseInfiniteLoopIfProxyReturnsEmptyArrayAndValidatorIsInitialized()
    {
        $element = $this->getMock(get_class($this->element), array('setValueOptions'));

        $options = array();

        $proxy = $this->getMock('DoctrineModule\Form\Element\Proxy');
        $proxy->expects($this->exactly(2))
            ->method('getValueOptions')
            ->will($this->returnValue($options));

        $element->expects($this->never())
            ->method('setValueOptions');

        $element->setProxy($proxy);
        $element->getInputSpecification();
        $this->assertEquals($options, $element->getValueOptions());
    }

    public function testGetValueOptionsDoesntInvokeProxyIfOptionsNotEmpty()
    {
        $element = new ObjectRadio();

        $options = array('foo' => 'bar');

        $proxy = $this->getMock('DoctrineModule\Form\Element\Proxy');
        $proxy->expects($this->once())
            ->method('getValueOptions')
            ->will($this->returnValue($options));

        $element->setProxy($proxy);

        $this->assertEquals($options, $element->getValueOptions());
        $this->assertEquals($options, $element->getValueOptions());
    }
}
