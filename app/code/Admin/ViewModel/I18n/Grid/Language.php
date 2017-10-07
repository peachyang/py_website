<?php

namespace Seahinet\Admin\ViewModel\I18n\Grid;

use Seahinet\Admin\ViewModel\Grid as PGrid;
use Seahinet\Lib\Model\Collection\Merchant;
use Seahinet\Lib\Session\Segment;

class Language extends PGrid
{

    protected function prepareCollection($collection = null)
    {
        $collection = new Merchant;
        $collection->join('core_store', 'core_store.merchant_id = core_merchant.id', ['store' => 'name', 'store_id' => 'id'], 'left')
                ->join('core_language', 'core_language.merchant_id = core_merchant.id', ['language' => 'name', 'language_id' => 'id'], 'left')
                ->columns(['merchant' => 'code', 'merchant_id' => 'id'])
                ->order('core_merchant.id, core_store.id, core_language.id');
        return $collection->load(true, true);
    }

    public function getUser()
    {
        $segment = new Segment('admin');
        return $segment->get('user');
    }

}
