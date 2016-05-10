<?php

namespace Seahinet\Cms\Source;

use Seahinet\Cms\Model\Collection\Category as Collection;
use Seahinet\Lib\Source\SourceInterface;
use Zend\Db\Sql\Predicate\NotIn;

class Category implements SourceInterface
{

    public function getSourceArray($except = [])
    {
        $collection = new Collection;
        if (!empty($except)) {
            $collection->where(new NotIn('cms_page.id', (array) $except));
        }
        $result = [];
        foreach ($collection as $category) {
            $result[$category['id']] = $category['name'];
        }
        return $result;
    }

}
