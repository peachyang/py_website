<?php

namespace Seahinet\Admin\ViewModel\Sales\View;

use Seahinet\Lib\ViewModel\Template;
use Seahinet\Sales\Model\Invoice as Model;

class Invoice extends Template
{

    protected $invoice = null;
    protected $status = null;
    protected $phase = null;

    public function getOrder()
    {
        if (is_null($this->invoice)) {
            $this->invoice = (new Model)->load($this->getQuery('id'));
        }
        return $this->invoice;
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


}
