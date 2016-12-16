<?php

namespace Seahinet\Promotion\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;

class Rule extends AbstractCollection
{

    protected function construct()
    {
        $this->init('promotion');
    }

    public function withStore($inColumns = false)
    {
        $this->select->join('promotion_in_store', 'promotion_in_store.promotion_id=promotion.id', $inColumns ? [] : ['store_id'], 'left');
        return $this;
    }

}
