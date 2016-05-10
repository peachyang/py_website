<?php

namespace Seahinet\Cms\Source;

use Seahinet\Cms\Model\Collection\Category as Collection;
use Seahinet\Lib\Source\SourceInterface;

class Category implements SourceInterface
{

    public function getSourceArray()
    {
        $collection = new Collection;
        $result = [];
        foreach ($collection as $category) {
            $result[$category['id']] = $category['name'];
        }
        return $result;
    }

}
