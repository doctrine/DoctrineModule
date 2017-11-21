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

namespace DoctrineModuleTest;

use DoctrineModule\ConfigProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests used to ensure ConfigProvider operates as expected
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  James Titcumb <james@asgrim.com>
 */
class ConfigProviderTest extends TestCase
{
    public function testInvokeHasCorrectKeys()
    {
        $config = (new ConfigProvider())->__invoke();

        self::assertInternalType('array', $config);

        self::assertArrayHasKey('doctrine', $config, 'Expected config to have "doctrine" array key');
        self::assertArrayHasKey('doctrine_factories', $config, 'Expected config to have "doctrine_factories" array key');
        self::assertArrayHasKey('dependencies', $config, 'Expected config to have "dependencies" array key');
        self::assertArrayHasKey('controllers', $config, 'Expected config to have "controllers" array key');
        self::assertArrayHasKey('route_manager', $config, 'Expected config to have "route_manager" array key');
        self::assertArrayHasKey('console', $config, 'Expected config to have "console" array key');

        // Config Provider should not have service_manager key; should only exist in ZF Module
        self::assertArrayNotHasKey('service_manager', $config, 'Config should not have "service_manager" array key');

        self::assertSame($config, unserialize(serialize($config)));
    }
}
