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

namespace DoctrineModuleTest\Component\Console\Output;

use PHPUnit_Framework_TestCase as TestCase;
use DoctrineModule\Component\Console\Output\PropertyOutput;

/**
 * Tests for {@see \DoctrineModule\Component\Console\Output\PropertyOutput}
 *
 * @license MIT
 * @author Aleksandr Sandrovskiy <a.sandrovsky@gmail.com>
 */
class PropertyOutputTest extends TestCase
{

    /**
     * @covers \DoctrineModule\Component\Console\Output\PropertyOutput
     */
    public function testWrite()
    {
        $message = 'message';

        $output = new PropertyOutput();
        $output->write($message);
        $this->assertEquals($message, $output->getMessage());
    }
    
    /**
     * @covers \DoctrineModule\Component\Console\Output\PropertyOutput
     */
    public function testWriteConcat()
    {
        $message = 'message';
        $message2 = 'message2';
        
        $output = new PropertyOutput();
        $output->write($message, PHP_EOL);
        $output->write($message2, PHP_EOL);
        
        $expected = $message . PHP_EOL . $message2 . PHP_EOL;
        $this->assertEquals($expected, $output->getMessage());
    }
}
