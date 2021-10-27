<?php

declare(strict_types=1);

namespace DoctrineModule;

use DoctrineModule\Component\Console\Output\PropertyOutput;
use Laminas\Console\Adapter\AdapterInterface as Console;
use Symfony\Component\Console\Input\StringInput;

use function interface_exists;
use function sprintf;
use function trigger_error;

use const PHP_VERSION_ID;

// phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
if (PHP_VERSION_ID > 80000) {
    /**
     * @internal
     * @deprecated 4.2.0 Usage of laminas/laminas-mvc-console is deprecated, integration will be removed in 5.0.0.
     *                   Please use ./vendor/bin/doctrine-module instead.
     */
    trait GetConsoleUsage
    {
    }
} else {
    /**
     * @internal
     * @deprecated 4.2.0 Usage of laminas/laminas-mvc-console is deprecated, integration will be removed in 5.0.0.
     *                   Please use ./vendor/bin/doctrine-module instead.
     *
     * @psalm-suppress DuplicateClass
     */
    trait GetConsoleUsage
    {
        /**
         * Prints console usage information for laminas-mvc-console
         */
        public function getConsoleUsage(Console $console): string
        {
            if (! interface_exists(Console::class)) {
                trigger_error(sprintf(
                    'Using %s requires the package laminas/laminas-mvc-console, which is currently not installed.',
                    __METHOD__
                ));

                return '';
            }

            $cli    = $this->serviceManager->get('doctrine.cli');
            $output = new PropertyOutput();

            $cli->run(new StringInput('list'), $output);

            return $output->getMessage();
        }
    }
}
