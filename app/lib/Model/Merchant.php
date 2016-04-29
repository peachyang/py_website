<?php

namespace Seahinet\Lib\Model;

use Seahinet\Lib\Model\Collection\Store as StoreCollection;

class Merchant extends AbstractModel
{

    protected function _construct()
    {
        $this->init('core_merchant', 'id', ['id', 'code', 'status']);
    }

    public function getStore($code = null)
    {
        $store = new StoreCollection;
        if (!is_null($code)) {
            $store->where(['merchant_id' => $this->getId(), 'code' => $code, 'status' => 1]);
        } else {
            $store->where(['merchant_id' => $this->getId(), 'is_default' => 1, 'status' => 1]);
        }
        $store->load();
        return new Store($store[0]);
    }

    public function getLanguage($code = null)
    {
        $lang = new LanguageCollection;
        if (!is_null($code)) {
            $lang->where(['merchant_id' => $this->getId(), 'code' => $code, 'status' => 1]);
        } else {
            $lang->where(['merchant_id' => $this->getId(), 'is_default' => 1, 'status' => 1]);
        }
        $lang->load();
        return new Language($lang[0]);
    }

}
