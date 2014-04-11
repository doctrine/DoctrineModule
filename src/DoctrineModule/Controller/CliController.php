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

namespace DoctrineModule\Controller;

use Symfony\Component\Console\Application;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ConsoleModel;
use DoctrineModule\Component\Console\Input\RequestInput;

/**
 * Index controller
 *
 * @license MIT
 * @author Aleksandr Sandrovskiy <a.sandrovsky@gmail.com>
 */
class CliController extends AbstractActionController
{
    /**
     * @var \Symfony\Component\Console\Application
     */
    protected $cliApplication;

    /**
     * @param \Symfony\Component\Console\Application $cliApplication
     */
    public function __construct(Application $cliApplication)
    {
        $this->cliApplication = $cliApplication;
    }

    /**
     * Index action - runs the console application
     */
    public function cliAction()
    {
        $exitCode = $this->cliApplication->run(new RequestInput($this->getRequest()));

        if (is_numeric($exitCode)) {
            $model = new ConsoleModel();
            $model->setErrorLevel($exitCode);

            return $model;
        }
    }
}
