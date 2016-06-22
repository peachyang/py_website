<?php

namespace Seahinet\Customer\Source;

use Seahinet\Customer\Model\Collection\Group as Collection;
use Seahinet\Lib\Source\SourceInterface;

class Group implements SourceInterface
{

    public function getSourceArray()
    {
        $collection = new Collection;
        $collection->columns(['id', 'name']);
        $record = [];
        foreach ($collection as $item) {
            $record[$item['id']] = $item['name'];
        }
        return $record;
    }

}
