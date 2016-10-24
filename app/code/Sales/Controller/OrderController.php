<?php

namespace Seahinet\Sales\Controller;

use Seahinet\Customer\Controller\AuthActionController;
use Seahinet\Lib\Session\Segment;
use Seahinet\Lib\ViewModel\Template;
use Seahinet\Sales\Model;

class OrderController extends AuthActionController
{

    public function listAction()
    {
        return $this->getLayout('sales_order_list');
    }

    public function reviewAction()
    {
        if ($id = $this->getRequest()->getQuery('id')) {
            $root = $this->getLayout('sales_order_review');
            $order = new Model\Order;
            $order->load($id);
            $root->getChild('head')->setTitle($this->translate('Review Order #%s', [$order->offsetGet('increment_id')]));
            $content = $root->getChild('content');
            $content->setVariable('title', $this->translate('Review Order #%s', [$order->offsetGet('increment_id')]));
            $content->getChild('main')->setVariable('model', $order);
            return $root;
        }
        return $this->redirectReferer('sales/order/list');
    }

    public function reviewPostAction()
    {
        
    }

    public function viewAction($handler = 'sales_order_view')
    {
        if ($id = $this->getRequest()->getQuery('id')) {
            $order = new Model\Order;
            $order->load($id);
            $segment = new Segment('customer');
            if ($order->offsetGet('customer_id') == $segment->get('customer')->getId()) {
                $root = $this->getLayout($handler);
                $root->getChild('head')->setTitle($this->translate('Order #%s', [$order->offsetGet('increment_id')], 'sales'));
                $root->getChild('main', true)->setVariable('order', $order);
                return $root;
            }
        }
        return $this->redirectReferer('sales/order/list');
    }

    public function invoiceAction()
    {
        if ($id = $this->getRequest()->getQuery('invoice')) {
            $model = new Model\Invoice;
            $model->load($id);
            if ($model->offsetGet('order_id') == $this->getRequest()->getQuery('id')) {
                $root = $this->viewAction('sales_order_invoice');
                if ($root instanceof Template) {
                    $root->getChild('pane', true)->setVariable('model', $model);
                }
                return $root;
            }
        }
        return $this->redirectReferer('sales/order/list');
    }

    public function shippmentAction()
    {
        if ($id = $this->getRequest()->getQuery('shippment')) {
            $model = new Model\Shipment;
            $model->load($id);
            if ($model->offsetGet('order_id') == $this->getRequest()->getQuery('id')) {
                $root = $this->viewAction('sales_order_shipment');
                if ($root instanceof Template) {
                    $root->getChild('pane', true)->setVariable('model', $model);
                }
                return $root;
            }
        }
        return $this->redirectReferer('sales/order/list');
    }

    public function creditMemoAction()
    {
        if ($id = $this->getRequest()->getQuery('creditmemo')) {
            $model = new Model\CreditMemo;
            $model->load($id);
            if ($model->offsetGet('order_id') == $this->getRequest()->getQuery('id')) {
                $root = $this->viewAction('sales_order_creditmemo');
                if ($root instanceof Template) {
                    $root->getChild('pane', true)->setVariable('model', $model);
                }
                return $root;
            }
        }
        return $this->redirectReferer('sales/order/list');
    }

}
