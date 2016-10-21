<?php

namespace Seahinet\Resource\Source;

use Seahinet\Resource\Model\Collection\Category as Collection;
use Seahinet\Lib\Source\SourceInterface;
use Zend\Db\Sql\Predicate\NotIn;
use Seahinet\Lib\Bootstrap;

class Category implements SourceInterface
{

    use \Seahinet\Lib\Traits\Container;

    public function getSourceArray($except = [])
    {
        $collection = new Collection;
        if (!empty($except)) {
            $collection->where(new NotIn('resource_category.id', (array) $except));
        }
        $result = [];
        foreach ($collection as $category) {
            $result[$category['id']] = $category['name'][Bootstrap::getLanguage()->getId()] ?? ($category['name'][0] ?? '');
        }
        return $result;
    }

}
