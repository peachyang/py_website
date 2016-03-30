<?php

namespace Seahinet\Lib\ViewModel\Renderer;

class PHPInclude implements RendererInterface
{

    public function render($file, $params = [])
    {
        if(file_exists($file)){
            return include $file;
        }
        return '';
    }

}
