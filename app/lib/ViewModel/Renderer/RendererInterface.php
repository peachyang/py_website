<?php

namespace Seahinet\Lib\ViewModel\Renderer;

interface RendererInterface
{

    /**
     * Render the specified file with params
     * 
     * @param string $file
     * @param array $params
     * @return string
     */
    public function render($file, $params = []);
}
