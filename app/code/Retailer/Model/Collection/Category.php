<?php

namespace Seahinet\Retailer\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;

class Category extends AbstractCollection
{
    
    protected function construct()
    {
        $this->init('retailer_category');
    }

}
