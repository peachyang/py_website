<?php

namespace Seahinet\Catalog\Source;

use Seahinet\Catalog\Model\Collection\Product\Type as Collection;
use Seahinet\Lib\Source\SourceInterface;

class Type implements SourceInterface
{

    public function getSourceArray()
    {
        $collection = new Collection;
        $collection->columns(['id', 'name']);
        $result = [];
        foreach ($collection as $item) {
            $result[$item['id']] = $item['name'];
        }
        return $result;
    }

}
