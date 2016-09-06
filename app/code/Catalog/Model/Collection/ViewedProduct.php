<?php

namespace Seahinet\Catalog\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;

class ViewedProduct extends AbstractCollection
{

    protected function construct()
    {
        $this->init('log_viewed_product');
    }

}
