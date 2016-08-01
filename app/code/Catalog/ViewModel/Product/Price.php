<?php

namespace Seahinet\Catalog\ViewModel\Product;

use Seahinet\Lib\ViewModel\Template;

class Price extends Template
{

    public function __construct()
    {
        $this->setTemplate('catalog/product/price');
    }
    
    public function getCurrency()
    {
        return $this->getContainer()->get('currency');
    }

}
