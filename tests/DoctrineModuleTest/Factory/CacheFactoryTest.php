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

namespace DoctrineModuleTest\Factory;

use DoctrineModule\Factory\CacheFactory;
use PHPUnit_Framework_TestCase as BaseTestCase;

/**
 * Test for {@see \DoctrineModule\Factory\CacheFactory}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 */
class CacheFactoryTest extends BaseTestCase
{
    /**
     * @covers \DoctrineModule\Factory\CacheFactory::create
     */
    public function testWillSetNamespace()
    {
        $factory = new CacheFactory;

        /* @var $service \Doctrine\Common\Cache\ArrayCache */
        $instance = $factory->create(array('namespace' => 'bar'));

        $this->assertInstanceOf('Doctrine\\Common\\Cache\\ArrayCache', $instance);
        $this->assertSame('bar', $instance->getNamespace());
    }
}
