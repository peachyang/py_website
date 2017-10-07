<?php

namespace Seahinet\Api\Source;

use Seahinet\Api\Model\Collection\Rest\Role as Collection;
use Seahinet\Lib\Source\SourceInterface;

class RestRole implements SourceInterface
{

    public function getSourceArray()
    {
        $collection = new Collection;
        $collection->columns(['id', 'name']);
        $record = [];
        foreach ($collection as $item) {
            if (!isset($record[$item['id']])) {
                $record[$item['id']] = [];
            }
            $record[$item['id']] = $item['name'];
        }
        return $record;
    }

}
