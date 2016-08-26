<?php

namespace Seahinet\Admin\ViewModel\Promotion\Edit;

use Seahinet\Lib\ViewModel\Template;

class Condition extends Template
{

    public function getOptions($source)
    {
        if(is_subclass_of($source, '\\Seahinet\\Lib\\Source\\SourceInterface')){
            return (new $source)->getSourceArray();
        }
        return [];
    }

}
