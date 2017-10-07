<?php

namespace Seahinet\Admin\ViewModel\Distribution;

use Seahinet\Lib\ViewModel\Template;

class Percentage extends Template
{

    private static $showTemplate = true;

    public function __construct()
    {
        $this->template = 'distribution/percentage';
    }

    public function setTemplate($template, $force = true)
    {
        return $this;
    }

    public function showTemplate()
    {
        $result = static::$showTemplate;
        static::$showTemplate = false;
        return $result;
    }

}
