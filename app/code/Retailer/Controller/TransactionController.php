<?php

namespace Seahinet\Retailer\Controller;

use Seahinet\Lib\Session\Segment;

class TransactionController extends AuthActionController
{

    public function indexAction()
    {
        $segment = new Segment('customer');

        if ($customerId = $segment->get('customer')->getId()) {
            $customer = new Cmodel;
            $customer->load($customerId);
            $root = $this->getLayout('retailer_store_settings');
            $root->getChild('main', true)->setVariable('customer', $customer);
            return $root;
        }
        return $root;
    }

    public function productAction()
    {
        $root = $this->getLayout('retailer_product');
        $order = Array(
            'type' => 'sold'
        );
        $root->getChild('main', true)->setVariable('subtitle', 'Sold Product')->setVariable('filter', $this->getRequest()->getQuery());
        return $root;
    }

    public function orderviewAction()
    {
        $order_id = $this->getRequest()->getQuery('id');
        if (empty($order_id) || !is_numeric($order_id)) {
            return $this->redirect('retailer/transaction/products/');
        }
        $root = $this->getLayout('retailer_order_view');
        return $root;
    }

    public function reviewAction()
    {
        return $this->getLayout('retailer_review');
    }

}
