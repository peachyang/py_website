<?php

namespace Seahinet\Catalog\Source;

use Seahinet\Catalog\Model\Collection\Category as Collection;
use Seahinet\Lib\Source\SourceInterface;

class Product implements SourceInterface
{

    public function getSourceArray()
    {
        $collection = new Collection;
        $result = [];
        foreach ($collection as $category) {
            $result[$category['id']] = $category['name'];
        }
        return $result;
    }

}
