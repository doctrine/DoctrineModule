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

namespace DoctrineModuleTest\Builder;

use DoctrineModule\Builder\DriverBuilder;
use PHPUnit_Framework_TestCase as BaseTestCase;
use Zend\ServiceManager\ServiceManager;

/**
 * Base test case to be used when a service manager instance is required
 */
class DriverBuilderTest extends BaseTestCase
{
    public function testCreateDriver()
    {
        $builder = new DriverBuilder;
        $driver = $builder->build(
            array(
                'class' => 'DoctrineModuleTest\Builder\Mock\MetadataDriverMock',
            )
        );
        $this->assertInstanceOf('DoctrineModuleTest\Builder\Mock\MetadataDriverMock', $driver);
    }

    public function testCreateDriverChain()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setInvokableClass('testDriver', 'DoctrineModuleTest\Builder\Mock\MetadataDriverMock');

        $builder = new DriverBuilder();
        $builder->setServiceLocator($serviceManager);
        $driver = $builder->build(
            array(
                'class' => 'Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain',
                'drivers' => array(
                    'Foo\Bar' => 'testDriver',
                    'Foo\Baz' => null,
                ),
            )
        );
        $this->assertInstanceOf('Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain', $driver);
        $drivers = $driver->getDrivers();
        $this->assertCount(1, $drivers);
        $this->assertArrayHasKey('Foo\Bar', $drivers);
        $this->assertInstanceOf('DoctrineModuleTest\Builder\Mock\MetadataDriverMock', $drivers['Foo\Bar']);
    }
}
