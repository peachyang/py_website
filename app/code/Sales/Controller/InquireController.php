<?php

namespace Seahinet\Sales\Controller;

use Seahinet\Lib\Controller\ActionController;
use Seahinet\Sales\Model\Order;

class InquireController extends ActionController
{

    public function inquireAction()
    {
        return $this->getLayout('sales_order_inquire');
    }

    public function viewAction()
    {
        if ($id = $this->getRequest()->getQuery('id')) {
            $root = $this->getLayout('sales_order_view');
            $order = new Order;
            $order->load($id);
            $root->getChild('head')->setTitle($this->translate('Order #%s', [$order->offsetGet('increment_id')], 'sales'));
            $root->getChild('main', true)->setVariable('order', $order);
            return $root;
        }
        return $this->redirectReferer('sales/order/list');
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

}
