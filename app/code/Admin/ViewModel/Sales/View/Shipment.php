<?php

namespace Seahinet\Admin\ViewModel\Sales\View;

use Seahinet\Lib\ViewModel\Template;
use Seahinet\Sales\Model\Shipment as Model;
use Seahinet\Customer\Model\Customer;

class Shipment extends Template
{

    protected $shipment = null;
    protected $status = null;
    protected $phase = null;

    public function getOrder()
    {
        if (is_null($this->shipment)) {
            $this->shipment = (new Model)->load($this->getQuery('id'));
        }
        return $this->shipment;
    }

    public function getCustomer()
    {
        if ($id = $this->getorder()->offsetGet('customer_id')) {
            $customer = new Customer;
            $customer->load($id);
            return $customer;
        }
        return null;
    }

    public function getCollection()
    {
        $collection = $this->getOrder()->getItems();
        return $collection;
    }

    public function getStatus()
    {
        return false;
    }

    public function getPhase()
    {
        if (is_null($this->phase)) {
            //$this->phase = $this->getStatus()->getPhase();
        }
        return $this->phase;
    }

    public function canCancel()
    {
        //return in_array($this->getPhase()->offsetGet('code'), ['pending', 'pending_payment']);
    }

    public function canHold()
    {
        //return $this->getPhase()->offsetGet('code') === 'processing';
    }

    public function canUnhold()
    {
        //return $this->getPhase()->offsetGet('code') === 'holded';
    }

    public function canInvoice()
    {
        /* if (in_array($this->getPhase()->offsetGet('code'), ['complete', 'canceled', 'closed', 'holded'])) {
            return false;
        }
        $orders = $this->getOrder()->getOrder();
        $qty = $this->getOrder()->getQty();
        foreach ($orders as $order) {
            foreach ($order->getItems() as $item) {
                $qty -= $item['qty'];
            }
        }
        return $qty > 0; */
    }

    public function canShip()
    {
        /* if (in_array($this->getPhase()->offsetGet('code'), ['complete', 'canceled', 'closed', 'holded'])) {
            return false;
        }
        $shipments = $this->getOrder()->getShipment();
        $qty = $this->getOrder()->getQty();
        foreach ($shipments as $shipment) {
            foreach ($shipment->getItems() as $item) {
                $qty -= $item['qty'];
            }
        }
        return $qty > 0; */
    }

}
