<?php

namespace Seahinet\Lib\ViewModel\Renderer;

class PHPInclude implements RendererInterface
{

    /**
     * Use include to render templates
     * 
     * @param string $file
     * @return string Rendered code
     */
    public function render($file, $params = [])
    {
        if (file_exists($file)) {
            return include $file;
        }
        return '';
    }

}
