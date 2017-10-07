<?php

namespace Seahinet\Retailer\Source;

use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Source\SourceInterface;
use Seahinet\Retailer\Model\Collection\Category as Collection;

class Category implements SourceInterface
{

    public function getSourceArray($storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = Bootstrap::getStore()->getId();
        }
        $collection = new Collection;
        $collection->where([
            'store_id' => $storeId
        ])->order('parent_id ASC, id ASC');
        $result = [];
        $collection->walk(function($item) use (&$result){
            $result[$item['id']] = $item->getName();
        });
        return $result;
    }

}
