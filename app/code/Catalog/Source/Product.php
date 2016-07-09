<?php

namespace Seahinet\Catalog\Source;

use Seahinet\Catalog\Model\Collection\Product as Collection;
use Seahinet\Lib\Source\SourceInterface;

class Product implements SourceInterface
{

    public function getSourceArray()
    {
        $collection = new Collection;
        $result = [];
        foreach ($collection as $product) {
            $result[$product['id']] = $product['name'];
        }
        return $result;
    }

}
