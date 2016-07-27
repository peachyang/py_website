<?php

namespace Seahinet\Catalog\ViewModel\Product;

use Seahinet\Lib\ViewModel\Template;

class Option extends Template
{

    public function getOptions()
    {
        if ($product = $this->getVariable('product')) {
            return $product->getOptions();
        }
        return [];
    }

    public function getCurrency()
    {
        return $this->getContainer()->get('currency');
    }

}
