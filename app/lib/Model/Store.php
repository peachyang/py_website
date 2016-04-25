<?php

namespace Seahinet\Lib\Model;

use Seahinet\Lib\Model\Collection\Language as LanguageCollection;

class Store extends AbstractModel
{

    protected function _construct()
    {
        $this->init('core_store', 'id', ['id', 'merchant_id', 'code', 'status']);
    }

    public function getLanguage($code = null)
    {
        $lang = new LanguageCollection;
        if (!is_null($code)) {
            $lang->where(['store_id' => $this->getId(), 'code' => $code, 'status' => 1]);
        } else {
            $lang->where(['store_id' => $this->getId(), 'is_default' => 1, 'status' => 1]);
        }
        $lang->load();
        return new Language($lang[0]);
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
