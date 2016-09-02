<?php

namespace Seahinet\Sales\Controller;

use Seahinet\Sales\Model\Collection\Order;
use Seahinet\Sales\Model\Order as OrderModel;
use Seahinet\Sales\Model\Collection\Invoice;
use Seahinet\Sales\Model\Collection\Shipment;
use Seahinet\Sales\Model\Collection\CreditMemo;
use Seahinet\Lib\Session\Segment;
use Seahinet\Customer\Controller\AuthActionController;

class OrderController extends AuthActionController
{

    public function orderAction()
    {
        $segment = new Segment('customer');
        $customerId = $segment->get('customer')->getId();
        $orders = new Order;
        $orders->where(['customer_id' => $customerId]);
        $root = $this->getLayout('sales_myorder');
        $root->getChild('main', true)->setVariable('orders', $orders);
        return $root;
    }

    protected function viewAction($id, $title, $key = 0)
    {
        $id = $this->getRequest()->getQuery('order_id');
        $key = intval($this->getRequest()->getQuery('key') - 1);
        $order = new OrderModel;
        $segment = new Segment('customer');
        $invoice = new Invoice;
        $shipment = new Shipment;
        $creditmemo = new CreditMemo;
        $order->load($id);
        $invoice->where(['order_id' => $id]);
        $shipment->where(['order_id' => $id]);
        $creditmemo->where(['order_id' => $id]);
        if ($order['customer_id'] !== $segment->get('customer')->getId()) {
            return $this->notFoundAction();
        }
        $root = $this->getLayout('sales_view');
        $root->getChild('main', true)->setVariable('order', $order)
                ->setVariable('invoice', $invoice->load()->toArray())
                ->setVariable('shipment', $shipment->load()->toArray())
                ->setVariable('creditmemo', $creditmemo->load()->toArray())
                ->setVariable('key', $key >= 0 ? $key : -1)
                ->setVariable('title', $title);
        return $root;
    }

    public function view_orderAction()
    {
        $id = $this->getRequest()->getQuery('order_id');
        return $this->viewAction($id, 'view_order');
    }

    public function view_invoiceAction()
    {
        $id = $this->getRequest()->getQuery('order_id');
        $key = intval($this->getRequest()->getQuery('key') - 1);
        return $this->viewAction($id, 'view_invoice', $key);
    }

    public function view_shipmentAction()
    {
        $id = $this->getRequest()->getQuery('order_id');
        $key = intval($this->getRequest()->getQuery('key') - 1);
        return $this->viewAction($id, 'view_shipment', $key);
    }

    public function view_creditmemoAction()
    {
        $id = $this->getRequest()->getQuery('order_id');
        $key = intval($this->getRequest()->getQuery('key') - 1);
        return $this->viewAction($id, 'view_creditmemo', $key);
    }

}
