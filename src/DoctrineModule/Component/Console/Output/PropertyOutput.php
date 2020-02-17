<?php

declare(strict_types=1);

namespace DoctrineModule\Component\Console\Output;

use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\Output;
use function function_exists;
use function getenv;
use function posix_isatty;
use const DIRECTORY_SEPARATOR;
use const PHP_EOL;
use const STDOUT;

/**
 * Output writing in class member variable
 */
class PropertyOutput extends Output
{
    /** @var string */
    private $message = '';

    /**
     * @param null $decorated
     */
    public function __construct(
        int $verbosity = self::VERBOSITY_NORMAL,
        $decorated = null,
        ?OutputFormatterInterface $formatter = null
    ) {
        if ($decorated === null) {
            $decorated = $this->hasColorSupport();
        }

        parent::__construct($verbosity, $decorated, $formatter);
    }

    // phpcs:disable SlevomatCodingStandard.TypeHints.ReturnTypeHint
    protected function doWrite(string $message, bool $newline)
    {
    // phpcs:enable SlevomatCodingStandard.TypeHints.ReturnTypeHint
        $this->message .= $message . ($newline === false ? '' : PHP_EOL);
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    protected function hasColorSupport() : bool
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            return getenv('ANSICON') !== false || getenv('ConEmuANSI') === 'ON';
        }

        return function_exists('posix_isatty') && @posix_isatty(STDOUT);
    }
}
