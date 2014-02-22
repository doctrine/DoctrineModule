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
 * and is licensed under the MIT license.
 */

namespace DoctrineModuleTest\Component\Console\Input;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Console\Request;
use DoctrineModule\Component\Console\Input\RequestInput;

/**
 * Tests for {@see \DoctrineModule\Component\Console\Input\RequestInput}
 *
 * @license MIT
 * @author Aleksandr Sandrovskiy <a.sandrovsky@gmail.com>
 */
class RequestInputTest extends TestCase
{
    /**
     * @covers \DoctrineModule\Component\Console\Input\RequestInput
     */
    public function testParamsCorrectlySetted()
    {
        $params = array(
            'scriptname.php',
            'list',
            '--help',
            '--foo=bar'
        );

        $request = new Request($params);

        $input = new RequestInput($request);

        array_shift($params);

        $this->assertTrue($input->hasParameterOption('list'));
        $this->assertTrue($input->hasParameterOption('--help'));
        $this->assertSame('bar', $input->getParameterOption('--foo'));
    }
}
