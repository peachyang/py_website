<?php

namespace Seahinet\Lib\Model;

use Seahinet\Lib\Model\Collection\Store;

class Merchant extends AbstractModel
{

    protected function _construct()
    {
        $this->init('core_merchant', 'id', ['id', 'code', 'status']);
    }

    public function getStore($code = null)
    {
        $store = new Store;
        if (!is_null($code)) {
            $store->where(['code' => $code, 'status' => 1]);
        } else {
            $store->where(['is_default' => 1, 'status' => 1]);
        }
        $store->load();
        return $store[0];
    }

}
