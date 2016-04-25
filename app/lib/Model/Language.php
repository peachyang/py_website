<?php

namespace Seahinet\Lib\Model;

class Language extends AbstractModel
{

    protected function _construct()
    {
        $this->init('core_language', 'id', ['id', 'store_id', 'code', 'name', 'status']);
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

    protected function afterSave()
    {
        $this->flushList('core_merchant\\');
        parent::afterSave();
    }

    protected function afterRemove()
    {
        $this->flushList('core_merchant\\');
        parent::afterRemove();
    }

}
