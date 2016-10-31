<?php

namespace Seahinet\Admin\ViewModel\Sales\View;

use Seahinet\Lib\ViewModel\Template;
use Seahinet\Sales\Model\Creditmemo as Model;
use Seahinet\Customer\Model\Customer;
use Seahinet\Sales\Model\Order;
use TCPDFBarcode;
use Pelago\Emogrifier;

class Creditmemo extends Template
{

    protected $creditmemo = null;
    protected $order = null;
    protected $status = null;
    protected $phase = null;

    public function getCreditMemo()
    {
        if (is_null($this->creditmemo)) {
            $this->creditmemo = (new Model)->load($this->getQuery('id'));
        }
        return $this->creditmemo;
    }

    public function getOrder()
    {
        if (is_null($this->creditmemo)) {
            $creditMemo = $this->getCreditMemo();
        }
        if (is_null($this->order)) {
            $this->order = (new Order)->load($this->creditmemo['order_id']);
        }
        return $this->order;
    }

    public function getCustomer()
    {
        if ($id = $this->getOrder()->offsetGet('customer_id')) {
            $customer = new Customer;
            $customer->load($id);
            return $customer;
        }
        return null;
    }

    public function getCollection()
    {
        $collection = $this->getCreditMemo()->getItems();
        return $collection;
    }

    public function getOrderModel()
    {
        error_reporting(E_ALL & ~E_NOTICE);
        $id = $this->getRequest()->getQuery('id');
        $invoice = (new Model)->load($id);
        $order = (new Order())->load($invoice['order_id']);
        return $order;
    }

}
