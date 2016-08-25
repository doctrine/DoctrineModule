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

use Zend\Mvc\Application;
use Zend\Stdlib\ArrayUtils;

ini_set('display_errors', true);
chdir(__DIR__);

$previousDir = '.';

while (!file_exists('config/application.config.php')) {
    $dir = dirname(getcwd());

    if ($previousDir === $dir) {
        throw new RuntimeException(
            'Unable to locate "config/application.config.php": ' .
            'is DoctrineModule in a subdir of your application skeleton?'
        );
    }

    $previousDir = $dir;
    chdir($dir);
}


if (is_readable('init_autoloader.php')) {
    include_once 'init_autoloader.php';
} elseif (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    include_once __DIR__ . '/../vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/../../../autoload.php')) {
    include_once __DIR__ . '/../../../autoload.php';
} else {
    throw new RuntimeException('Error: vendor/autoload.php could not be found. Did you run php composer.phar install?');
}

$appConfig = include 'config/application.config.php';
if (file_exists('config/development.config.php')) {
    $appConfig = ArrayUtils::merge($appConfig, include 'config/development.config.php');
}

$application = Application::init($appConfig);

/* @var $cli \Symfony\Component\Console\Application */
$cli = $application->getServiceManager()->get('doctrine.cli');
exit($cli->run());
