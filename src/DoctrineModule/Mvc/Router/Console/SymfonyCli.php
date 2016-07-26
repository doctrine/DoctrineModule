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

namespace DoctrineModule\Mvc\Router\Console;

use Symfony\Component\Console\Application;
use Zend\Stdlib\RequestInterface as Request;
use Zend\Mvc\Router\RouteMatch as V2RouteMatch;
use Zend\Router\RouteMatch;
use Zend\Console\Request as ConsoleRequest;

/**
 * Route matching commands in Symfony CLI
 *
 * @license MIT
 * @author Aleksandr Sandrovskiy <a.sandrovsky@gmail.com>
 */
abstract class SymfonyCli
{
    /**
     * @var \Symfony\Component\Console\Application
     */
    protected $cliApplication;

    /**
     * Default values.
     *
     * @var array
     */
    protected $defaults;

    /**
     * Constructor
     *
     * @param \Symfony\Component\Console\Application $cliApplication
     * @param array                                  $defaults
     */
    public function __construct(Application $cliApplication, array $defaults = array())
    {
        $this->cliApplication = $cliApplication;
        $this->defaults       = $defaults;
    }

    /**
     * {@inheritDoc}
     */
    public function match(Request $request)
    {
        if (!$request instanceof ConsoleRequest) {
            return null;
        }

        $params = $request->getParams()->toArray();

        if (! isset($params[0]) || ! $this->cliApplication->has($params[0])) {
            return null;
        }

        return $this->createRouteMatch($this->defaults);
    }

    /**
     * Disabled.
     *
     * {@inheritDoc}
     *
     * @throws \BadMethodCallException this method is disabled
     */
    public function assemble(array $params = array(), array $options = array())
    {
        throw new \BadMethodCallException('Unsupported');
    }

    /**
     * {@inheritDoc}
     */
    public function getAssembledParams()
    {
        return array();
    }

    /**
     * Disabled.
     *
     * {@inheritDoc}
     *
     * @throws \BadMethodCallException this method is disabled
     */
    public static function factory($options = array())
    {
        throw new \BadMethodCallException('Unsupported');
    }

    protected function createRouteMatch(array $params = [])
    {
        $class = class_exists(V2RouteMatch::class) ? V2RouteMatch::class : RouteMatch::class;
        return new $class($params);
    }
}
