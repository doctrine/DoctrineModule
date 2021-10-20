<?php

declare(strict_types=1);

namespace DoctrineModuleTest\Controller\Mock;

use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FailingCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('fail')
            ->addOption('exception', null, null, 'fail with an exception');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->getOption('exception')) {
            throw new RuntimeException();
        }

        return 1;
    }
}
