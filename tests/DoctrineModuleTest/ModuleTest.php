$cliMock = $this->getMock('Symfony\Component\Console\Application', array(
            'setDispatcher',
            'run'
        ), array(), '', false, false);
        $cliMock->expects($this->any())
            ->method('run')
            ->will($this->returnCallback(function ($input, $output)
        {
            if ($input == 'list') {
                $output->write('start', true);
                $output->write('Line2', true);
                $output->write('Line3');
                $output->write('Line4');
                $output->write('end');
            }
        }));
        
        $sm = $this->eventMock->getTarget()->getServiceManager();
        $cliOriginal = $sm->get('doctrine.cli');
        
        $sm->setAllowOverride(true);
        $sm->setService('doctrine.cli', $cliMock);
        
        $module = new Module();
        $module->onBootstrap($this->eventMock);
        
        $console = $this->getMock('Zend\Console\Adapter\AbstractAdapter');
        $actual = $module->getConsoleUsage($console);
        
        $this->assertStringMatchesFormat("start%aend", $actual);
        
        $sm->setService('doctrine.cli', $cliOriginal);
        $sm->setAllowOverride(false);
