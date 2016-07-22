<?php

namespace Seahinet\Catalog\ViewModel\Product;

use Seahinet\Lib\ViewModel\Template;

class View extends Template
{

    protected $product = null;

    function getProduct()
    {
        return $this->product;
    }

    function setProduct($product)
    {
        $this->product = $product;
        return $this;
    }

    public function getOptions()
    {
        return $this->product;
    }

    public function setOptions(){
    $this->product = $option;
    return $this->product;
    }

    public function getPriceBox()
    {
        $box = new Price;
        $box->setVariable('product', $this->product);
        return $box;
    }

}
