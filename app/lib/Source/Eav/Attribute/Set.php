<?php

namespace Seahinet\Lib\Source\Eav\Attribute;

use Seahinet\Lib\Model\Collection\Eav\Attribute\Set as Collection;
use Seahinet\Lib\Source\SourceInterface;

class Set implements SourceInterface
{

    protected $entityType = '';

    public function getSourceArray()
    {
        $collection = new Collection;
        $collection->columns(['id', 'name']);
        if ($this->entityType) {
            $collection->join('eav_entity_type', 'eav_entity_type.id=type_id', [], 'right')
                    ->where(['eav_entity_type.code' => $this->entityType]);
        }
        $result = [];
        foreach ($collection as $item) {
            $result[$item['id']] = $item['name'];
        }
        return $result;
    }

}
