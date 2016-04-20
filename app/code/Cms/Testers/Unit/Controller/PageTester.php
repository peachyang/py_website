<?php

namespace Seahinet\Cms\Testers\Unit\Controller\Page;

class PageTester extends \PHPUnit_Framework_TestCase
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
        $this->assertInstanceOf('\\Seahinet\\Lib\\Http\\Response', $this->controller->indexAction());
        $this->assertInstanceOf('\\Seahinet\\Lib\\ViewModel\\Root', $this->controller->setOption('page', $this->pageMock)->indexAction());
    }

}

?>