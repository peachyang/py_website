<?php
namespace Seahinet\Cms\Testers\Unit\Route\Page;
use Curl\Curl;
use Seahinet\tester\Bootstrap;

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
		$this->route = new \Seahinet\Cms\Route\Page();
		$this->curl=new Curl();
	}
	
	public function testRoutePage(){
	    $_SERVER=array(
    "SCRIPT_FILENAME" =>"/home/html/ecomv2admin/index.php",
    "REQUEST_METHOD" => "GET",
    "SCRIPT_NAME" => "/index.php",
    "REQUEST_URI" => "/test.html",
    "DOCUMENT_URI" => "/index.php",
    "DOCUMENT_ROOT" => "/home/html/ecomv2admin",
    "SERVER_PROTOCOL" => "HTTP/1.1",
    "GATEWAY_INTERFACE" => "CGI/1.1",
    "SERVER_SOFTWARE" => "nginx/1.8.1",
    "SERVER_NAME" => "ecomv2.lh.com",
    "REDIRECT_STATUS" => "200",
    "HTTP_HOST" => "ecomv2.lh.com",
	"QUERY_STRING" => "",
    "PHP_SELF" => "/index.php");
	 //   var_dump($_SERVER);
	Bootstrap::init($_SERVER);

	 $request=new \Seahinet\Lib\Http\Request($_SERVER);
	 //$request->setOption($_SERVER
	//var_dump(Bootstrap::getContainer()->get('config')['global/base_url']);
	 $this->assertSame($this->route->match($request), $this->curl->get('http://ecomv2.lh.com/test.html'));
	}
	
	
}

?>