<?php

namespace Seahinet\Admin\ViewModel\Promotion\Edit;

use Seahinet\Lib\ViewModel\Template;
use Seahinet\Promotion\Model\Collection\Condition as Collection;

class Condition extends Template
{

    public function getOptions($source)
    {
        if (is_subclass_of($source, '\\Seahinet\\Lib\\Source\\SourceInterface')) {
            return (new $source)->getSourceArray(true);
        }
        return [];
    }

    public function getCollection()
    {
        if ($this->getQuery('id')) {
            $collection = new Collection;
            $collection->where(['promotion_id' => $this->getQuery('id')]);
            $result = [];
            foreach ($collection as $item) {
                $pid = (int) $item->offsetGet('parent_id');
                if (!isset($result[$pid])) {
                    $result[$pid] = [];
                }
                $result[$pid][] = $item->toArray();
            }
            return $result;
        }
        return [];
    }

}
