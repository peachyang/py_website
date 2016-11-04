<?php

namespace Seahinet\Admin\ViewModel\Sales\View;

use Seahinet\Customer\Model\Customer;
use Seahinet\Lib\ViewModel\Template;
use Seahinet\Sales\Model\Rma as Model;
use Seahinet\Sales\Model\Order;
use Seahinet\Sales\Source\Refund\Service;

class Refund extends Template
{

    protected $refund = null;
    protected $order = null;
    protected $status = null;
    protected $phase = null;
    protected $service = null;

    public function getService($key)
    {
        if (is_null($this->service)) {
            $this->service = (new Service)->getSourceArray();
        }
        return $this->service[$key] ?? '';
    }

    public function getRefund()
    {
        if (is_null($this->refund)) {
            $this->refund = (new Model)->load($this->getQuery('id'));
        }
        return $this->refund;
    }

    public function getVariable($key, $default = '')
    {
        return $key === 'model' ? $this->getRefund() : parent::getVariable($key, $default);
    }

    public function getCurrency()
    {
        return $this->getContainer()->get('currency');
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

    public function getOrderModel()
    {
        $id = $this->getRequest()->getQuery('id');
        $refund = (new Model)->load($id);
        $order = (new Order())->load($refund['order_id']);
        return $order;
    }

    public function getHandler()
    {
        $template = $this->getRefund()['service'] . '-' . $this->getRefund()['status'];
        if (in_array($template, [
                    '0-0', '1-0', '2-0',
                    '1-2', '2-2', '2-3'
                ])) {
            $viewModel = new static;
            $viewModel->setTemplate('admin/sales/refund/' . $template);
        }
        return $viewModel ?? '';
    }

}
