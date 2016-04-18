<?php
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
		$this->route = new \Seahinet\Cms\Route;
		$this->request=new \Seahinet\Lib\Http\Request;

	}
	
	public function testRoutePage(){
		$request=new \Seahinet\Lib\Http\Request();
		
	}
	
	
}

?>