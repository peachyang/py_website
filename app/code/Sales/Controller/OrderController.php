<?php

namespace Seahinet\Sales\Controller;

use Exception;
use Seahinet\Catalog\Model\Product\Review;
use Seahinet\Customer\Controller\AuthActionController;
use Seahinet\Lib\Bootstrap;
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
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $result = $this->validateForm($data, ['order_id', 'review']);
            if ($result['error'] === 0) {
                try {
                    $segment = new Segment('customer');
                    $order = new Model\Order;
                    $order->load($data['order_id']);
                    if (!$order->getId() || !$order->canReview() ||
                            ($segment->get('hasLoggedIn') && $order->offsetGet('customer_id') !== $segment->get('customer')->getId()) ||
                            (!$segment->get('hasLoggedIn') && $order->offsetGet('customer_id'))) {
                        throw new Exception('Invalid Order Id');
                    }
                    $files = $this->getRequest()->getUploadedFile();
                    $path = BP . 'pub/review/';
                    if (!is_dir($path)) {
                        mkdir($path, 0644, true);
                    }
                    foreach ($data['review'] as $productId => $content) {
                        $images = [];
                        if (!empty($files['image'][$productId])) {
                            $count = 0;
                            foreach ($files['image'][$productId] as $file) {
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
                        }
                        $review = new Review;
                        $review->setData([
                            'product_id' => $productId,
                            'customer_id' => $segment->get('hasLoggedIn') ? $segment->get('customer')->getId() : null,
                            'order_id' => $data['order_id'],
                            'language_id' => Bootstrap::getLanguage()->getId(),
                            'subject' => $data['subject'] ?? '',
                            'content' => $content,
                            'images' => json_encode($images),
                            'anonymous' => $data['anonymous'] ?? 0,
                            'rating' => ($data['rating'][0] ?? []) + ($data['rating'][$productId] ?? [])
                        ])->save();
                    }
                    $result['message'][] = ['message' => 'We have received your review. Thanks for your support.', 'level' => 'success'];
                } catch (Exception $e) {
                    $this->getContainer()->get('log')->logException($e);
                    $result['error'] = 1;
                    $result['message'][] = ['message' => 'An error detected while saving. Please try again later.', 'level' => 'danger'];
                }
            }
        }
        return $this->response($result ?? ['error' => 0, 'message' => []], 'sales/order/list/', 'customer');
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
