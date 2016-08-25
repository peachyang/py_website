<?php

namespace Seahinet\Customer\Source;

use Seahinet\Customer\Model\Collection\Customer as Collection;
use Seahinet\Lib\Session\Segment;
use Seahinet\Lib\Source\SourceInterface;

class Customer implements SourceInterface
{

    public function getSourceArray()
    {
        $collection = new Collection;
        $result = [];
        $user = (new Segment('admin'))->get('user');
        if ($user->getStore()) {
            $collection->where(['store_id' => $user->getStore()->getId()]);
        }
        foreach ($collection as $item) {
            $result[$item['id']] = $item['username'];
        }
        return $result;
    }
}






