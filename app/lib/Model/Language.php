<?php

namespace Seahinet\Lib\Model;

class Language extends AbstractModel
{

    protected function _construct()
    {
        $this->init('core_language', 'id', ['id', 'store', 'code', 'status']);
    }

    public function getStore()
    {
        if ($this->isLoaded) {
            $store = new Store;
            $store->load($this->offsetGet('store_id'));
            return $store;
        }
        return null;
    }

}
