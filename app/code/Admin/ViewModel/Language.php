<?php

namespace Seahinet\Admin\ViewModel;

use Seahinet\Lib\Model\Collection\Merchant;

class Language extends Grid
{

    protected function prepareCollection($collection = null)
    {
        $collection = new Merchant;
        $collection->join('core_store', 'merchant_id = core_merchant.id', ['store' => 'code', 'store_id' => 'id'], 'left')
                ->join('core_language', 'store_id = core_store.id', ['language' => 'name', 'language_id' => 'id'], 'left')
                ->columns(['merchant' => 'code', 'merchant_id' => 'id'])
                ->order('core_merchant.id, core_store.id');
        return $collection;
    }

}
