<?php

namespace Seahinet\Catalog\Model\Collection\Product;

use Seahinet\Lib\Model\AbstractCollection;

class Review extends AbstractCollection
{

    protected function construct()
    {
        $this->init('review');
    }

    protected function afterLoad($result)
    {
        foreach ($result as &$item) {
            $content = @gzdecode($item['content']);
            if ($content !== false) {
                $item['content'] = $content;
            }
        }
        parent::afterLoad($result);
    }

}
