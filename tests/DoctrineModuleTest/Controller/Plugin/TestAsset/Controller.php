<?php

namespace DoctrineModuleTest\Controller\Plugin\TestAsset;

use Zend\Http\Request as HttpRequest;
use Zend\Mvc\Controller\AbstractController;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\MvcEvent;

class Controller extends AbstractController
{
    public function setRequest(HttpRequest $request)
    {
        $this->request = $request;
    }

    public function onDispatch(MvcEvent $mvcEvent)
    {

    }
}