<?php

namespace Seahinet\Admin\ViewModel\Sales\View;

use Seahinet\Lib\ViewModel\Template;
use Seahinet\Sales\Model\Rma as Model;
use TCPDFBarcode;
use Zend\Db\Adapter\Platform\Oracle;
use Seahinet\Sales\Model\Order;
use Seahinet\Customer\Model\Customer;
use Pelago\Emogrifier;

class Refund extends Template
{

    protected $refund = null;
    protected $order = null;
    protected $status = null;
    protected $phase = null;

    public function getRefund()
    {
        if (is_null($this->refund)) {
            $this->refund = (new Model)->load($this->getQuery('id'));
        }
        return $this->refund;
    }

    public function getOrder()
    {
        if (is_null($this->order)) {
            $invoice = (new Model)->load($this->getQuery('id'));
            $this->order = (new Order)->load($invoice['order_id']);
        }
        return $this->order;
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
    

    public function getOrderModel(){
        $id = $this->getRequest()->getQuery('id');
        $refund = (new Model)->load($id);
        $order = (new Order())->load($refund['order_id']);
        return $order;
    }

}
