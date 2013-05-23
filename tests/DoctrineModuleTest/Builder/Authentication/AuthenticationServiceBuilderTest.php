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

namespace DoctrineModuleTest\Builder\Authentication;

use DoctrineModule\Authentication\Adapter\ObjectRepositoryAdapter;
use DoctrineModule\Builder\Authentication\AuthenticationServiceBuilder;
use PHPUnit_Framework_TestCase as BaseTestCase;
use Zend\Authentication\Storage\NonPersistent as NonPersistentStorage;
use Zend\ServiceManager\ServiceManager;

class AuthenticationServiceBuilderTest extends BaseTestCase
{
    public function testWillInstantiateFromFQCN()
    {

        $builder = new AuthenticationServiceBuilder;

        $serviceManager = new ServiceManager();
        $serviceManager->setService('testAdapter', new ObjectRepositoryAdapter);
        $serviceManager->setService('testStorage', new NonPersistentStorage);

        $builder->setServiceLocator($serviceManager);

        $authenticationService = $builder->build(
            array(
                'adapter' => 'testAdapter',
                'storage' => 'testStorage'
            )
        );
        $this->assertInstanceOf('Zend\Authentication\AuthenticationService', $authenticationService);
    }
}
