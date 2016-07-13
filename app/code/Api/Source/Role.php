<?php

namespace Seahinet\Api\Source;

use Seahinet\Api\Model\Collection\Soap\Role as Collection;
use Seahinet\Lib\Source\SourceInterface;

class Role implements SourceInterface
{

    public function getSourceArray()
    {
        $collection = new Collection;
        $collection->columns(['id', 'name']);
        $record = [];
        foreach ($collection as $item) {
            if(!isset($record[$item['id']])){
                $record[$item['id']] = [];
            }
            $record[$item['id']] = $item['name'];
        }
        return $record;
    }
}
