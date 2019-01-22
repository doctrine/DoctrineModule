<?php

namespace DoctrineModule\Component\Console\Output;

use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;

/**
 * Output writing in class member variable
 *
 * @license MIT
 * @author Aleksandr Sandrovskiy <a.sandrovsky@gmail.com>
 */
class PropertyOutput extends Output
{
    /**
     * @var string
     */
    private $message = '';

    /**
     * @param int $verbosity
     * @param null $decorated
     * @param \Symfony\Component\Console\Formatter\OutputFormatterInterface $formatter
     */
    public function __construct(
        $verbosity = self::VERBOSITY_NORMAL,
        $decorated = null,
        OutputFormatterInterface $formatter = null
    ) {
        if (null === $decorated) {
            $decorated = $this->hasColorSupport();
        }

        parent::__construct($verbosity, $decorated, $formatter);
    }

    /**
     * @param string $message
     * @param bool $newline
     */
    protected function doWrite($message, $newline)
    {
        $this->message .= $message . ($newline === false ? '' : PHP_EOL);
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return bool
     */
    protected function hasColorSupport()
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            return false !== getenv('ANSICON') || 'ON' === getenv('ConEmuANSI');
        }

        return function_exists('posix_isatty') && @posix_isatty(STDOUT);
    }
}
