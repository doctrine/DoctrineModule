<?php

declare(strict_types=1);

namespace DoctrineModuleTest\Component\Console\Input;

use DoctrineModule\Component\Console\Input\RequestInput;
use Laminas\Console\Request;
use PHPUnit\Framework\TestCase;

use function array_shift;

/**
 * Tests for {@see \DoctrineModule\Component\Console\Input\RequestInput}
 */
class RequestInputTest extends TestCase
{
    /**
     * @covers \DoctrineModule\Component\Console\Input\RequestInput
     */
    public function testParamsCorrectlySetted(): void
    {
        $params = [
            'scriptname.php',
            'list',
            '--help',
            '--foo=bar',
        ];

        $request = new Request($params);

        $input = new RequestInput($request);

        array_shift($params);

        $this->assertTrue($input->hasParameterOption('list'));
        $this->assertTrue($input->hasParameterOption('--help'));
        $this->assertSame('bar', $input->getParameterOption('--foo'));
    }
}
