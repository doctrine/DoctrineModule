<?php

declare(strict_types=1);

namespace DoctrineModuleTest\Service\TestAsset;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DummyCliCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('app:dummy-command')
            ->setDescription('A dummy command for testing purposes.');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln([
            'Dummy Command',
            '=============',
            '',
        ]);

        return Command::SUCCESS;
    }
}
