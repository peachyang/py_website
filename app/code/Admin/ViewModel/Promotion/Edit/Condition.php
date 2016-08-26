<?php

namespace Seahinet\Admin\ViewModel\Promotion\Edit;

use Seahinet\Lib\ViewModel\Template;
use Seahinet\Promotion\Model\Collection\Condition as Collection;

class Condition extends Template
{

    public function getOptions($source)
    {
        if (is_subclass_of($source, '\\Seahinet\\Lib\\Source\\SourceInterface')) {
            return (new $source)->getSourceArray();
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
                if (!isset($result[(int) $item->offsetGet('parent_id')])) {
                    $result[(int) $item->offsetGet('parent_id')] = [];
                }
                $result[(int) $item->offsetGet('parent_id')][] = $item->toArray();
            }
            return $result;
        }
        return [];
    }

}
