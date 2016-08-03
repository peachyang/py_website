<?php

namespace Seahinet\Sales\Source\Order;

use Seahinet\Lib\Source\SourceInterface;
use Seahinet\Sales\Model\Collection\Order\Status as Collection;

class Status implements SourceInterface
{

    public function getSourceArray()
    {
        $collection = new Collection;
        $result = [];
        $collection->order('phase_id, id');
        foreach ($collection as $item) {
            $result[$item['id']] = $item['name'];
        }
        return $result;
    }

}
