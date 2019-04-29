<?php

namespace DoctrineModuleTest\Component\Console\Input;

use PHPUnit\Framework\TestCase;
use Zend\Console\Request;
use DoctrineModule\Component\Console\Input\RequestInput;

/**
 * Tests for {@see \DoctrineModule\Component\Console\Input\RequestInput}
 *
 * @license MIT
 * @author Aleksandr Sandrovskiy <a.sandrovsky@gmail.com>
 */
class RequestInputTest extends TestCase
{
    /**
     * @covers \DoctrineModule\Component\Console\Input\RequestInput
     */
    public function testParamsCorrectlySetted()
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
