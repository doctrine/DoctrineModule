<?php

declare(strict_types=1);

namespace DoctrineModule\Controller;

use DoctrineModule\Component\Console\Input\RequestInput;
use Laminas\Mvc\Console\View\ViewModel;
use Laminas\Mvc\Controller\AbstractActionController;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;

use function class_exists;
use function is_numeric;
use function sprintf;
use function trigger_error;

if (! class_exists(AbstractActionController::class)) {
    trigger_error(sprintf(
        'Using %s requires the package laminas/laminas-mvc-console, which is currently not installed.',
        CliController::class
    ));

    return;
}

/**
 * Index controller
 *
 * @deprecated 4.2.0 CliController is deprecated and will be removed in 5.0.0. Please use the doctrine-module bin script.
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

    public function setOutput(OutputInterface $output): void
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
