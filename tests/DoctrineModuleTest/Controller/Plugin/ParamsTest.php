<?php

namespace DoctrineModuleTest\Controller\Plugin;

use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Controller\Plugin\Params;
use DoctrineModuleTest\Controller\Plugin\TestAsset\Controller;
use DoctrineModuleTest\Controller\Plugin\TestAsset\Object;
use PHPUnit_Framework_TestCase as TestCase;
use Zend\Http\Request as HttpRequest;
use Zend\Mvc\Controller\Plugin\Params as ZendParams;
use Zend\Stdlib\Parameters;

class ParamsTest extends TestCase
{
    /**
     * @var Object
     */
    protected $object2;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Controller
     */
    protected $controller;

    /**
     * @var ZendParams
     */
    protected $zendParams;

    /**
     * @var Params
     */
    protected $ormParams;

    protected function setUp()
    {
        parent::setUp();

        $object2 = new Object(2, 'Jane');

        $objectManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');

        $objectManager->method('find')
            ->willReturnMap([
                [Object::class, '2', $object2],
                [Object::class, '3', null],
            ]);

        $controller = new Controller;
        $zendParams = new ZendParams;
        $zendParams->setController($controller);
        $ormParams = new Params($objectManager, $zendParams);

        $controller->getPluginManager()->setFactory('params', function () use ($zendParams) {
            return $zendParams;
        });

        $controller->getPluginManager()->setFactory('ormParams', function () use ($ormParams) {
            return $ormParams;
        });

        $this->object2 = $object2;
        $this->objectManager = $objectManager;
        $this->controller = $controller;
        $this->ormParams = $ormParams;
    }

    public function testGetPluginIfNoParams()
    {
        $this->assertEquals($this->ormParams, $this->controller->ormParams());
    }

    public function testExistingObject()
    {
        $request = new HttpRequest;
        $request->setQuery(new Parameters([
            'object' => '2',
        ]));
        $this->controller->setRequest($request);

        $this->assertEquals($this->object2, $this->controller->ormParams()->fromQuery(Object::class));
    }

    public function testNonExistingObject()
    {
        $request = new HttpRequest;
        $request->setQuery(new Parameters([
            'object' => '3',
        ]));
        $this->controller->setRequest($request);

        $this->assertEquals(null, $this->controller->ormParams()->fromQuery(Object::class));
    }

    public function testDefaultFallback()
    {
        $request = new HttpRequest;
        $request->setQuery(new Parameters([
            'object' => '3',
        ]));
        $this->controller->setRequest($request);

        $this->assertEquals('default', $this->controller->ormParams()->fromQuery(Object::class, null, 'default'));
    }

    public function testAlternativeParamName()
    {
        $request = new HttpRequest;
        $request->setQuery(new Parameters([
            'alternative-name' => '2',
        ]));
        $this->controller->setRequest($request);

        $this->assertEquals($this->object2, $this->controller->ormParams()->fromQuery(Object::class, 'alternative-name'));
    }

    public function testPost()
    {
        $request = new HttpRequest;
        $request->setPost(new Parameters([
            'object' => '2',
        ]));
        $this->controller->setRequest($request);

        $this->assertEquals($this->object2, $this->controller->ormParams()->fromPost(Object::class));
    }
}
