<?php

namespace Seahinet\Resources\Source;

use Seahinet\Resources\Model\Collection\Category as Collection;
use Seahinet\Lib\Source\SourceInterface;
use Zend\Db\Sql\Predicate\NotIn;

class Category implements SourceInterface
{

    public function getSourceArray($except = [])
    {
        $collection = new Collection;
        if (!empty($except)) {
            $collection->where(new NotIn('resources_category.id', (array) $except));
        }
        $result = [];
        foreach ($collection as $category) {
            $result[$category['id']] = $category['id'];
        }
        return $result;
    }

}
