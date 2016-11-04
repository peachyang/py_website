<?php

namespace Seahinet\Sales\ViewModel\Refund;

use Seahinet\Lib\ViewModel\Template;
use Seahinet\Sales\Source\Refund\{
    Reason,
    Service
};

class Apply extends Template
{

    public function getCurrency()
    {
        return $this->getContainer()->get('currency');
    }

    public function getReasons()
    {
        return (new Reason)->getSourceArray();
    }

    public function getServices()
    {
        return (new Service)->getSourceArray();
    }

    public function getItems()
    {
        $order = $this->getVariable('model');
        $items = [];
        foreach ($order->getItems() as $item) {
            $items[$item->getId()] = $item;
        }
        $memos = $order->getCreditMemo();
        foreach ($memos as $memo) {
            foreach ($memo->getItems() as $item) {
                $items[$item['item_id']]['qty'] -= $item['qty'];
            }
        }
        return $items;
    }

}
