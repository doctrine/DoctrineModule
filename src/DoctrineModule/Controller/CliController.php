<?php

namespace DoctrineModule\Controller;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Laminas\Mvc\Console\View\ViewModel;
use Laminas\Mvc\Controller\AbstractActionController;
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
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    /**
     * @param \Symfony\Component\Console\Application $cliApplication
     */
    public function __construct(Application $cliApplication)
    {
        $this->cliApplication = $cliApplication;
    }

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Index action - runs the console application
     */
    public function cliAction()
    {
        $exitCode = $this->cliApplication->run(new RequestInput($this->getRequest()), $this->output);

        if (is_numeric($exitCode)) {
            $model = new ViewModel();
            $model->setErrorLevel($exitCode);

            return $model;
        }
    }
}
