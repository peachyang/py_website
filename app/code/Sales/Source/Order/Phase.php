<?php

namespace Seahinet\Sales\Source\Order;

use Seahinet\Lib\Source\SourceInterface;
use Seahinet\Sales\Model\Collection\Order\Phase as Collection;

class Phase implements SourceInterface
{

    public function getSourceArray()
    {
        $collection = new Collection;
        $result = [];
        foreach ($collection as $item) {
            $result[$item['id']] = $item['name'];
        }
        return $result;
    }

}
