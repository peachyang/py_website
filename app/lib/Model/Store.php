<?php

namespace Seahinet\Lib\Model;

use Seahinet\Lib\Model\Collection\Language as LanguageCollection;

class Store extends AbstractModel
{

    protected function construct()
    {
        $this->init('core_store', 'id', ['id', 'merchant_id', 'code', 'name', 'status']);
    }

    public function getMerchant()
    {
        if ($this->isLoaded) {
            $merchant = new Merchant;
            $merchant->load($this->offsetGet('merchant_id'));
            return $merchant;
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
