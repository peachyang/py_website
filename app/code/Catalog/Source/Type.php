<?php

namespace Seahinet\Catalog\Source;

use Seahinet\Catalog\Model\Collection\Product\Type as Collection;
use Seahinet\Lib\Source\SourceInterface;

class Type implements SourceInterface
{

    use \Seahinet\Lib\Traits\Container,
        \Seahinet\Lib\Traits\Translate;

    public function getSourceArray()
    {
        $collection = new Collection;
        $collection->columns(['id', 'name']);
        $result = [];
        foreach ($collection as $item) {
            $result[$item['id']] = $this->translate($item['name'], [], 'catalog');
        }
        return $result;
    }

}
