<?php

namespace DoctrineModuleTest\Controller\Mock;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

class FailingCommand extends Command
{
    protected function configure()
    {
        $this->setName('fail')
            ->addOption('exception', null, null, 'fail with an exception');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('exception')) {
            throw new \RuntimeException();
        } else {
            return 1;
        }
    }
}
