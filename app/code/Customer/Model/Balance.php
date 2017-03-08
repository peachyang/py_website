<?php

namespace Seahinet\Customer\Model;

use Seahinet\Lib\Model\AbstractModel;

class Balance extends AbstractModel
{

    use \Seahinet\Balance\Traits\Recalc;

    protected function construct()
    {
        $this->init('customer_balance', 'id', ['id', 'customer_id', 'order_id', 'amount', 'comment', 'status']);
    }

    protected function afterSave()
    {
        if ($this->storage['status']) {
            $this->recalc($this->storage['customer_id']);
        }
        parent::afterSave();
    }

}
