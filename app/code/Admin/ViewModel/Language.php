<?php

namespace Seahinet\Admin\ViewModel;

use Seahinet\Lib\Model\Collection\Merchant;

class Language extends Grid
{

    protected function prepareCollection($collection = null)
    {
        $collection = new Merchant;
        $collection->join('core_store', 'merchant_id = core_merchant.id', ['store' => 'code'], 'left');
        $collection->join('core_language', 'store_id = core_store.id', ['language' => 'code'], 'left');
        $collection->columns(['merchant' => 'code']);
        return parent::prepareCollection($collection);
    }

}
