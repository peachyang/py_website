<?php

namespace Seahinet\Admin\ViewModel;

use Seahinet\Lib\Model\Collection\Merchant;

class Language extends Grid
{

    protected function prepareCollection($collection = null)
    {
        $collection = new Merchant;
        $collection->join('admin_store', 'merchant_id = admin_merchant.id', ['store' => 'code'], 'left');
        $collection->join('admin_language', 'store_id = admin_store.id', ['language' => 'code'], 'left');
        $collection->columns(['merchant' => 'code']);echo $collection->getSqlString($this->getContainer()->get('dbAdapter')->getPlatform());die();
        return parent::prepareCollection($collection);
    }

}
