<?php

namespace Seahinet\Lib\Source\Eav\Attribute;

use Seahinet\Lib\Model\Collection\Eav\Attribute\Set as Collection;
use Seahinet\Lib\Source\SourceInterface;

class Set implements SourceInterface
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
