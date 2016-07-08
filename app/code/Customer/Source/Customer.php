<?php

namespace Seahinet\Customer\Source;

use Seahinet\Customer\Model\Collection\Customer as Collection;
use Seahinet\Lib\Source\SourceInterface;

class Customer implements SourceInterface
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
