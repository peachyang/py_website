<?php
namespace Seahinet\Cms\Testers\Unit\Controller\Page;

class PageTester  extends \PHPUnit_Framework_TestCase{
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
		
		$this->pageMock=$this->getMockBuilder('Seahinet\Cms\Controller\PageController')->disableOriginalConstructor()
            ->getMock();
	}
	
/***
	public function testIndexAction(){		
		
		$this->pageMock->expects($this->once())
		->method('indexAction')
		->willReturnSelf();
		
		
		
		$this->assertSame($this->pageMock, $this->controller->indexAction());
	}
**/
}


	
?>