<?php

namespace Seahinet\Lib\Source;

use Seahinet\Lib\Model\Collection\Language as Collection;

class Language implements SourceInterface
{

    public function getSourceArray()
    {
        $collection = new Collection;
        $collection->join('core_store', 'core_store.id=store_id', ['store' => 'code'], 'left');
        $collection->where(['core_store.status' => 1, 'core_language.status' => 1]);
        $result = [];
        foreach ($collection as $item) {
            if (!isset($result[$item['store']])) {
                $result[$item['store']] = [];
            }
            $result[$item['store']][$item['id']] = $item['name'];
        }
        return $result;
    }

}
