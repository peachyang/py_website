<?php

namespace Seahinet\Sales\Model;

use Seahinet\Lib\Model\AbstractModel;
use Seahinet\Sales\Model\Rma\Item;
use Seahinet\Sales\Model\Collection\Order\Item as ItemCollection;

class Rma extends AbstractModel
{

    protected $order = null;
    protected $items = null;

    protected function construct()
    {
        $this->init('sales_rma', 'id', ['id', 'order_id', 'customer_id', 'currency', 'amount', 'carrier', 'track_number', 'reason', 'service', 'status', 'created_at', 'updated_at']);
    }

    protected function afterSave()
    {
        if (!empty($this->storage['qty'])) {
            foreach ($this->storage['qty'] as $id => $qty) {
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

    public function addComment($data)
    {
        if ($this->getId()) {
            if (is_scalar($data)) {
                $data = ['comment' => $data];
            }
            $tableGateway = $this->getTableGateway('log_rma');
            $tableGateway->insert(['rma_id' => $this->getId()] + $data);
        }
        return $this;
    }

    public function getComment()
    {
        if ($this->getId()) {
            $tableGateway = $this->getTableGateway('log_rma');
            $tableGateway->select(['rma_id' => $this->getId()]);
        }
        return $this;
    }

    public function getOrder()
    {
        if (is_null($this->order)) {
            if (!empty($this->storage['order_id'])) {
                $order = new Order;
                $order->load($this->storage['order_id']);
                if ($order->getId()) {
                    $this->order = $order;
                }
            }
        }
        return $this->order;
    }

    public function getItems($force = false)
    {
        if ($force || is_null($this->items)) {
            $items = new ItemCollection();
            $items->join('sales_rma_item', 'sales_order_item.id=sales_rma_item.item_id', ['refunded_qty' => 'qty'], 'right')
                    ->where(['rma_id' => $this->getId()]);
            $result = [];
            $items->walk(function($item) use (&$result) {
                $result[$item['id']] = $item;
            });
            $this->items = $result;
            if ($force) {
                return $items;
            }
        }
        return $this->items;
    }

}
