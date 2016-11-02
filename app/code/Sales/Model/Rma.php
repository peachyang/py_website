<?php

namespace Seahinet\Sales\Model;

use Seahinet\Lib\Model\AbstractModel;
use Seahinet\Sales\Model\Rma\Item;

class Rma extends AbstractModel
{

    protected function construct()
    {
        $this->init('sales_rma', 'id', ['id', 'order_id', 'customer_id', 'amount', 'carrier', 'track_number', 'reason', 'comment', 'status', 'created_at', 'updated_at']);
    }

    protected function afterSave()
    {
        if (!empty($this->storage['qty'])) {
            foreach($this->storage['qty'] as $id => $qty){
                $item = new Item;
                $item->setData([
                    'rma_id' => $this->getId(),
                    'item_id' => $id,
                    'qty' => $qty
                ])->save();
            }
        }
        parent::afterSave();
    }

}
