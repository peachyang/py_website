<?php

namespace Seahinet\Resource\Source;

use Seahinet\Resource\Model\Collection\Category as Collection;
use Seahinet\Lib\Source\SourceInterface;
use Zend\Db\Sql\Predicate\NotIn;

class Category implements SourceInterface
{

    public function getSourceArray($except = [])
    {
        $collection = new Collection;
        if (!empty($except)) {
            $collection->where(new NotIn('Resource_category.id', (array) $except));
        }
        $result = [];
        foreach ($collection as $category) {
            $result[$category['id']] = $category['id'];
        }
        return $result;
    }

}
