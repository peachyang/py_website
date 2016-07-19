<?php

namespace Seahinet\Cms\Tests\Route;

use Curl\Curl;

class PageTest extends \PHPUnit_Framework_TestCase
{

    use \Seahinet\Lib\Traits\Container;

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
        $this->curl = new Curl();
    }

    public function testMatch()
    {
        $request = $this->getContainer()->get('request');
        $uri = $request->getUri();
        $route = new \Seahinet\Cms\Route\Page();
        $this->assertInstanceOf('\\Seahinet\\Lib\\Route\\RouteMatch', $route->match($request->withUri($uri->withPath('/'))));
        $this->assertEquals(false, $route->match($request->withUri($uri->withPath('/test'))));
    }

}

?>