<?php

namespace Seahinet\Article\Source;

use Seahinet\Article\Model\Collection\Product as Collection;
use Seahinet\Lib\Session\Segment;
use Seahinet\Lib\Source\SourceInterface;

class Product implements SourceInterface
{

    public function getSourceArray()
    {
        $collection = new Collection;
        $result = [];
        $user = (new Segment('admin'))->get('user');
        if ($user->getStore()) {
            $collection->where(['store_id' => $user->getStore()->getId()]);
        }
        foreach ($collection as $product) {
            $result[$product['id']] = $product['name'];
        }
        return $result;
    }

}
