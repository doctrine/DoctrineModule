<?php

declare(strict_types=1);

namespace DoctrineModuleTest\Component\Console\Output;

use DoctrineModule\Component\Console\Output\PropertyOutput;
use PHPUnit\Framework\TestCase;
use const PHP_EOL;

/**
 * Tests for {@see \DoctrineModule\Component\Console\Output\PropertyOutput}
 */
class PropertyOutputTest extends TestCase
{
    /**
     * @covers \DoctrineModule\Component\Console\Output\PropertyOutput
     */
    public function testWrite() : void
    {
        $message = 'message';

        $output = new PropertyOutput();
        $output->write($message);
        $this->assertEquals($message, $output->getMessage());
    }

    /**
     * @covers \DoctrineModule\Component\Console\Output\PropertyOutput
     */
    public function testWriteConcat() : void
    {
        $message  = 'message';
        $message2 = 'message2';

        $output = new PropertyOutput();
        $output->write($message, true);
        $output->write($message2, true);

        $expected = $message . PHP_EOL . $message2 . PHP_EOL;
        $this->assertEquals($expected, $output->getMessage());
    }
}
