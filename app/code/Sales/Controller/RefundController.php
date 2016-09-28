<?php

namespace Seahinet\Sales\Controller;

use Seahinet\Lib\Controller\ActionController;
use Seahinet\Lib\Session\Segment;
use Seahinet\Sales\Model\Order as OrderModel;
use Seahinet\Sales\Model\Order\Phase;
use Seahinet\Sales\Model\Rma;

class RefundController extends ActionController
{

    use \Seahinet\Lib\Traits\DB;

    public function indexAction()
    {
        $root = $this->getLayout('sales_refund');
        return $root;
    }

    public function saveAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $result = $this->validateForm($data, ['order_id', 'comment']);
            if ($result['error'] === 0) {
                $segment = new Segment('customer');
                if ($segment->get('hasLoggedIn')) {
                    $customerId = $segment->get('customer')->getID();
                }
                $order = (new OrderModel)->load($data['order_id'], isset($customerId) ? 'id' : 'increment_id');
                if (isset($customerId)) {
                    if ($customerId !== $order['customer_id']) {
                        $result['error'] = 1;
                        $result['message'][] = ['message' => 'Invalid order ID', 'level' => 'danger'];
                    }
                } else if ($order['customer_id']) {
                    $result['error'] = 1;
                    $result['message'][] = ['message' => 'Invalid order ID', 'level' => 'danger'];
                }
                if ($order->getId() && $order->canRefund(false)) {
                    $refund = new Rma($data);
                    $refund->setData([
                        'order_id' => $order['id'],
                        'customer_id' => $customerId ?? null
                    ]);
                    try {
                        $this->beginTransaction();
                        $refund->save();
                        $status = (new Phase)->load('holded', 'code')->getDefaultStatus()->getId();
                        if ($order->offsetGet('status_id') != $status) {
                            $order->setData('status_id', $status)->save();
                        }
                        $result['message'][] = ['message' => 'We have received your application. The customer service will contact you as soon as possible. Thanks for your support.', 'level' => 'success'];
                        $this->commit();
                    } catch (Exception $e) {
                        $this->rollback();
                        $result['error'] = 1;
                        $result['message'][] = ['message' => 'An error detected. Please contact us or try again later.', 'level' => 'danger'];
                    }
                } else {
                    $result['error'] = 1;
                    $result['message'][] = ['message' => 'Invalid order ID', 'level' => 'danger'];
                }
            }
        }
        return $this->response($result ?? ['error' => 0, 'message' => []], 'sales/refund/', 'customer');
    }

}
