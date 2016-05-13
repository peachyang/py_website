<?php

namespace Seahinet\Lib\Model;

use Zend\Db\Sql\Where;

class Store extends AbstractModel
{

    protected function construct()
    {
        $this->init('core_store', 'id', ['id', 'merchant_id', 'code', 'name', 'status', 'is_default']);
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
        if ($this->storage['is_default']) {
            $where = new Where;
            $where->notEqualTo('id', $this->getId())
                    ->equalTo('is_default', 1)
                    ->equalTo('merchant_id', $this->storage['merchant_id']);
            $this->tableGateway->update(['is_default' => 0], $where);
            $this->getCacheObject()->delete($this->getCacheKey(), 'DATA_');
        }
        $this->flushList('core_merchant');
        parent::afterSave();
    }

    protected function afterRemove()
    {
        $this->flushList('core_merchant');
        parent::afterRemove();
    }

}
