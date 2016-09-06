<?php

namespace Seahinet\Retailer\ViewModel\Edit;

use Seahinet\Admin\ViewModel\Catalog\Edit\Product as Pview;

class Product extends Pview
{
    public function __construct()
    {
        $this->setTemplate('retailer/productEdit');
    }
}