<?php

namespace Seahinet\Lib\Source;

use Seahinet\Lib\Model\Collection\Language as Collection;

class Language implements SourceInterface
{

    public function getSourceArray()
    {
        $collection = new Collection;
        $collection->join('core_merchant', 'core_merchant.id=merchant_id', ['merchant' => 'code'], 'left');
        $collection->where(['core_merchant.status' => 1, 'core_language.status' => 1]);
        $result = [];
        foreach ($collection as $item) {
            if (!isset($result[$item['merchant']])) {
                $result[$item['merchant']] = [];
            }
            $result[$item['merchant']][$item['id']] = $item['name'];
        }
        return $result;
    }

}
