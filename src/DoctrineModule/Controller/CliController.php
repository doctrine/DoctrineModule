<?php

declare(strict_types=1);

namespace DoctrineModule\Controller;

use DoctrineModule\Component\Console\Input\RequestInput;
use Laminas\Mvc\Console\View\ViewModel;
use Laminas\Mvc\Controller\AbstractActionController;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use function is_numeric;

/**
 * Index controller
 */
class CliController extends AbstractActionController
{
    /** @var Application */
    protected $cliApplication;

    /** @var OutputInterface */
    protected $output;

    public function __construct(Application $cliApplication)
    {
        $this->cliApplication = $cliApplication;
    }

    public function setOutput(OutputInterface $output) : void
    {
        $this->output = $output;
    }

    /**
     * Index action - runs the console application
     *
     * @return mixed
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
