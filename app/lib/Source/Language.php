<?php

namespace Seahinet\Lib\Source;

use Seahinet\Lib\Model\Collection\Language as Collection;

class Language implements SourceInterface
{

    public function getSourceArray()
    {
        $collection = new Collection;
        $collection->where(['core_language.status' => 1]);
        $result = [];
        foreach ($collection as $item) {
            $result[$item['id']] = $item['name'];
        }
        return $result;
    }

}
