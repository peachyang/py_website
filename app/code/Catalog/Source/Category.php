<?php

namespace Seahinet\Catalog\Source;

use Seahinet\Catalog\Model\Collection\Category as Collection;
use Seahinet\Lib\Session\Segment;
use Seahinet\Lib\Source\SourceInterface;

class Category implements SourceInterface
{

    public function getSourceArray($isBackend = true)
    {
        $collection = new Collection;
        $result = [];
        if ($isBackend) {
            $segment = new Segment('admin');
            if ($segment->get('hasLoggedIn') && $segment->get('user')->getStore()) {
                $collection->where(['store_id' => $user->getStore()->getId()]);
            }
        }
        foreach ($collection as $category) {
            $result[$category['id']] = $category['name'];
        }
        return $result;
    }

}
