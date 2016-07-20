<?php

namespace Seahinet\Cms\Tests\Controller\Page;

class PageTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var /Seahinet/Cms/Controller/PageController
     */
    protected $controller;

    /**
     * @var /Seahinet/Cms/Controller/PageController
     */
    protected $pageMock;

    protected function setUp()
    {
        $this->controller = new \Seahinet\Cms\Controller\PageController();
        $this->pageMock = $this->getMockBuilder('\\Seahinet\\Cms\\Model\\Page')->disableOriginalConstructor()
                ->getMock();
    }

    public function testIndexAction()
    {
        $this->assertInstanceOf('\\Seahinet\\Lib\\ViewModel\\Root', $this->controller->indexAction());
        $this->assertInstanceOf('\\Seahinet\\Lib\\ViewModel\\Root', $this->controller->setOption('page', $this->pageMock)->indexAction());
    }

}
