<?php

namespace Seahinet\CMS\Route;

use Seahinet\Lib\Http\Request;
use Seahinet\Lib\Route\RouteInterface;
use Seahinet\Lib\Route\RouteMatch;

class Page implements RouteInterface
{

    public function match(Request $request)
    {
        $path = trim($request->getUri()->getPath(), '/');
        if (substr($path, -5) === '.html') {
            $path = substr($path, 0, -5);
        } else if (substr($path, -4) === '.htm') {
            $path = substr($path, 0, -4);
        } else {
            return false;
        }
        $parts = explode('/', $path);
        do{
            $part = array_pop($parts);
            
        }while(count($parts));
        return false;
    }

    public function serialize()
    {
        
    }

    public function unserialize($serialized)
    {
        
    }

}
