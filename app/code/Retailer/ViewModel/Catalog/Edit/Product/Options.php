<?php

namespace Seahinet\Retailer\ViewModel\Catalog\Edit\Product;

use Seahinet\Lib\Source\Eav\Attribute\Input;

class Options extends Tab
{

    protected $inputOptions = [];

    public function getInputOptions()
    {
        if (empty($this->inputOptions)) {
            $this->inputOptions = (new Input)->getSourceArray();
        }
        return $this->inputOptions;
    }

}
