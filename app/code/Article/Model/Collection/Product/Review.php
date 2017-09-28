<?php

namespace Seahinet\Article\Model\Collection\Product;

use Seahinet\Lib\Model\AbstractCollection;

class Review extends AbstractCollection
{

    protected function construct()
    {
        $this->init('article_review');
    }

    protected function afterLoad(&$result)
    {
        foreach ($result as &$item) {
            $content = @gzdecode($item['content']);
            if ($content !== false) {
                $item['content'] = $content;
            }
            $reply = @gzdecode($item['reply']);
            if ($reply !== false) {
                $item['reply'] = $reply;
            }
        }
        return parent::afterLoad($result);
    }

}
