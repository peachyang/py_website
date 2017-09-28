<?php

namespace Seahinet\Article\ViewModel\Product;

use Seahinet\Lib\ViewModel\Template;

class Price extends Template
{

    public function __construct()
    {
        $this->setTemplate('article/product/price');
    }

    public function getCurrency()
    {
        return $this->getContainer()->get('currency');
    }

}
