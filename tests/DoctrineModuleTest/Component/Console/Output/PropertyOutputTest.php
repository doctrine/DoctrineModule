<?php

namespace DoctrineModuleTest\Component\Console\Output;

use PHPUnit\Framework\TestCase;
use DoctrineModule\Component\Console\Output\PropertyOutput;

/**
 * Tests for {@see \DoctrineModule\Component\Console\Output\PropertyOutput}
 *
 * @license MIT
 * @author Aleksandr Sandrovskiy <a.sandrovsky@gmail.com>
 */
class PropertyOutputTest extends TestCase
{

    /**
     * @covers \DoctrineModule\Component\Console\Output\PropertyOutput
     */
    public function testWrite()
    {
        $message = 'message';

        $output = new PropertyOutput();
        $output->write($message);
        $this->assertEquals($message, $output->getMessage());
    }

    /**
     * @covers \DoctrineModule\Component\Console\Output\PropertyOutput
     */
    public function testWriteConcat()
    {
        $message  = 'message';
        $message2 = 'message2';

        $output = new PropertyOutput();
        $output->write($message, PHP_EOL);
        $output->write($message2, PHP_EOL);

        $expected = $message . PHP_EOL . $message2 . PHP_EOL;
        $this->assertEquals($expected, $output->getMessage());
    }
}
