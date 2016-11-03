<?php

namespace Seahinet\Sales\Controller;

use Seahinet\Lib\Controller\ActionController;
use Seahinet\Lib\Session\Segment;
use Seahinet\Sales\Model\{
    Order,
    Order\Phase,
    Rma
};

class RefundController extends ActionController
{

    use \Seahinet\Lib\Traits\DB;

    public function indexAction()
    {
        return $this->getLayout('sales_refund');
    }

    public function applyAction()
    {
        if ($id = $this->getRequest()->getQuery('id')) {
            $order = new Order;
            $order->load($id);
            if ($order->getId() && $order->canRefund(false)) {
                $root = $this->getLayout('sales_refund_apply');
                $root->getChild('main', true)->setVariable('model', $order);
                return $root;
            }
        }
        return $this->notFoundAction();
    }

    public function viewAction()
    {
        if ($id = $this->getRequest()->getQuery('id')) {
            $rma = new Rma;
            $rma->load($id);
            if ($rma->getId()) {
                $root = $this->getLayout('sales_refund_view');
                $root->getChild('main', true)->setVariable('model', $rma);
                return $root;
            }
        }
        return $this->redirectReferer('sales/refund/');
    }

    public function saveAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $result = $this->validateForm($data, ['order_id', 'reason', 'qty', 'comment']);
            if ($result['error'] === 0) {
                $segment = new Segment('customer');
                $order = (new Order)->load($data['order_id']);
                if ($segment->get('hasLoggedIn')) {
                    $customerId = $segment->get('customer')->getId();
                    if ($customerId !== $order['customer_id']) {
                        $result['error'] = 1;
                        $result['message'][] = ['message' => $this->translate('Invalid order ID'), 'level' => 'danger'];
                    }
                } else if (!$order['customer_id']) {
                    $result['error'] = 1;
                    $result['message'][] = ['message' => $this->translate('Invalid order ID'), 'level' => 'danger'];
                }
                if ($order->getId() && $order->canRefund(false)) {
                    $refund = new Rma($data);
                    $refund->setData([
                        'order_id' => $order->getId(),
                        'customer_id' => $customerId ?? null
                    ]);
                    $images = [];
                    try {
                        $path = BP . 'pub/upload/refund/';
                        if (!is_dir($path)) {
                            mkdir($path, 0755, true);
                        }
                        $count = 0;
                        foreach ((array) $this->getRequest()->getUploadedFile() as $file) {
                            if ($file->getError() === UPLOAD_ERR_OK && $count++ < 5) {
                                $newName = $file->getClientFilename();
                                while (file_exists($path . $newName)) {
                                    $newName = preg_replace('/(\.[^\.]+$)/', random_int(0, 9) . '$1', $newName);
                                    if (strlen($newName) >= 120) {
                                        throw new Exception('The file is existed.');
                                    }
                                }
                                $file->moveTo($path . $newName);
                                $images[] = $newName;
                            }
                        }
                        $this->beginTransaction();
                        $refund->save()->addComment([
                            'comment' => $data['comment'],
                            'image' => json_encode($images)
                        ]);
                        $status = (new Phase)->load('holded', 'code')->getDefaultStatus()->getId();
                        if ($order->offsetGet('status_id') != $status) {
                            $order->setData('status_id', $status)->save();
                        }
                        $result['message'][] = ['message' => $this->translate('We have received your application. The customer service will contact you as soon as possible. Thanks for your support.'), 'level' => 'success'];
                        $this->commit();
                    } catch (Exception $e) {
                        $this->rollback();
                        $result['error'] = 1;
                        $result['message'][] = ['message' => $this->translate('An error detected. Please contact us or try again later.'), 'level' => 'danger'];
                    }
                } else {
                    $result['error'] = 1;
                    $result['message'][] = ['message' => $this->translate('Invalid order ID'), 'level' => 'danger'];
                }
            }
        }
        return $this->response($result ?? ['error' => 0, 'message' => []], 'sales/refund/', 'customer');
    }

}
