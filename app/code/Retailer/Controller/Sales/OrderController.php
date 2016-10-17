<?php

namespace Seahinet\Retailer\Controller\Sales;

use DateTime;
use Exception;
use Seahinet\Customer\Model\Address;
use Seahinet\Retailer\Controller\AuthActionController;
use Seahinet\Lib\Session\Segment;
use Seahinet\Sales\Model\Collection\Order as Collection;
use Seahinet\Sales\Model\Collection\Order\Status;
use Seahinet\Sales\Model\Order as Model;
use Seahinet\Sales\Model\Order\Status\History;
use TCPDF;

class OrderController extends AuthActionController
{

    public function chartAction()
    {
        $filter = $this->getRequest()->getQuery('filter', 'd');
        $collection = new Collection;
        $collection->columns(['created_at']);
        if ($filter === 'd') {
            $filted = array_fill(1, 24, 0);
        } else if ($filter === 'm') {
            $filted = array_fill(1, 30, 0);
        } else if ($filter === 'y') {
            $filted = array_fill(1, 12, 0);
        } else {
            $filted = [];
            $from1 = strtotime($this->getRequest()->getQuery('from1', 0));
            $from2 = strtotime($this->getRequest()->getQuery('from2', 0));
            $to1 = strtotime($this->getRequest()->getQuery('to1', 0));
            $to2 = strtotime($this->getRequest()->getQuery('to2', 0));
        }
        $result = [
            'amount' => 0,
            'daily' => 0,
            'monthly' => 0,
            'yearly' => 0,
            'filted' => $filted,
            'keys' => array_keys($filted)
        ];
        if ($collection->count()) {
            $current = new DateTime;
            $keys = [];
            foreach ($collection as $item) {
                $time = new DateTime(date(DateTime::RFC3339, strtotime($item['created_at'])));
                $diff = $current->diff($time);
                if ($diff->d < 1) {
                    $result['daily'] ++;
                    if ($filter === 'd') {
                        $result['filted'][$diff->h + 1] ++;
                    }
                }
                if ($diff->m < 1) {
                    $result['monthly'] ++;
                    if ($filter === 'm') {
                        $result['filted'][$diff->d + 1] ++;
                    }
                }
                if ($diff->y < 1) {
                    $result['yearly'] ++;
                    if ($filter === 'y') {
                        $result['filted'][$diff->m + 1] ++;
                    }
                }
                if ($filter === 'c') {
                    $ts = $time->getTimestamp();
                    $key = date('Y-m-d', $ts);
                    $result['compared'] = [];
                    if ($ts >= $from1 && $ts <= $to1) {
                        if (!isset($result['filted'][$key])) {
                            $result['filted'][$key] = 0;
                        }
                        $result['compared'][$key] = null;
                        $result['filted'][$key] ++;
                        $keys[$key] = 1;
                    }
                    if ($ts >= $from2 && $ts <= $to2) {
                        if (!isset($result['compared'][$key])) {
                            $result['compared'][$key] = 0;
                        }
                        $result['compared'][$key] ++;
                        $keys[$key] = 1;
                    }
                }
                $result['amount'] ++;
            }
            if (!empty($keys)) {
                $result['keys'] = array_keys($keys);
            }
        }
        return $result;
    }

    public function indexAction()
        {
            $root = $this->getLayout('admin_sales_order_list');
            return $root;
        }
    
        public function viewAction()
        {
            if ($id = $this->getRequest()->getQuery('id')) {
                return $this->getLayout('admin_sales_order_view');
            }
            return $this->notFoundAction();
        }

    public function cancelAction()
    {
        if ($id = $this->getRequest()->getQuery('id')) {
            $result = ['error' => 0, 'message' => []];
            try {
                $status = new Status;
                $status->join('sales_order_phase', 'sales_order_phase.id=sales_order_status.phase_id', [])
                        ->where(['is_default' => 1, 'sales_order_phase.code' => 'canceled'])
                        ->limit(1);
                $count = 0;
                $statusId = $status[0]->getId();
                $dispatcher = $this->getContainer()->get('eventDispatcher');
                $this->beginTransaction();
                $order = new Model;
                $order->load($id);
                if ($order->canCancel()) {
                    $history = new History;
                    $history->setData([
                        'admin_id' => null,
                        'order_id' => $id,
                        'status_id' => $statusId,
                        'status' => $status[0]->offsetGet('name')
                    ])->save();
                    $order->setData('status_id', $statusId)->save();
                    $dispatcher->trigger('order.cancel.after', ['model' => $order]);
                    $count ++;
                }
                $this->commit();
                $result['message'][] = ['message' => $this->translate('%d order(s) has been canceled.', [count((array) $id)]), 'level' => 'success'];
                return $this->response($result, $this->getRequest()->getHeader('HTTP_REFERER'), 'retailer');
            } catch (Exception $e) {
                $this->rollback();
                $this->getContainer()->get('log')->logException($e);
                $result['error'] = 1;
                $result['message'][] = ['message' => $this->translate('An error detected while canceling orders.'), 'level' => 'danger'];
            }
        }
        return $this->redirectReferer('retailer/transaction/product/');
    }

    public function holdAction()
    {
        if ($id = $this->getRequest()->getQuery('id')) {
            $result = ['error' => 0, 'message' => []];
            try {
                $status = new Status;
                $status->join('sales_order_phase', 'sales_order_phase.id=sales_order_status.phase_id', [])
                        ->where(['is_default' => 1, 'sales_order_phase.code' => 'holded'])
                        ->limit(1);
                $count = 0;
                $statusId = $status[0]->getId();
                $this->beginTransaction();
                foreach ((array) $id as $i) {
                    $order = new Model;
                    $order->load($i);
                    if ($order->canHold()) {
                        $history = new History;
                        $history->setData([
                            'admin_id' => null,
                            'order_id' => $i,
                            'status_id' => $statusId,
                            'status' => $status[0]->offsetGet('name')
                        ])->save();
                        $order->setData('status_id', $statusId)
                                ->save();
                        $count ++;
                    }
                }
                $this->commit();
                $result['message'][] = ['message' => $this->translate('%d order(s) has been holded.', [count((array) $id)]), 'level' => 'success'];
                return $this->response($result, $this->getRequest()->getHeader('HTTP_REFERER'), 'retailer');
            } catch (Exception $e) {
                $this->rollback();
                $this->getContainer()->get('log')->logException($e);
                $result['error'] = 1;
                $result['message'][] = ['message' => $this->translate('An error detected while holding orders.'), 'level' => 'danger'];
            }
        }
        return $this->redirectReferer('retailer/transaction/product/');
    }

    public function unholdAction()
    {
        if ($id = $this->getRequest()->getQuery('id')) {
            $result = ['error' => 0, 'message' => []];
            try {
                $status = new Status;
                $status->join('sales_order_phase', 'sales_order_phase.id=sales_order_status.phase_id', [])
                        ->where(['is_default' => 1, 'sales_order_phase.code' => 'processing'])
                        ->limit(1);
                $count = 0;
                $this->beginTransaction();
                foreach ((array) $id as $i) {
                    $order = new Model;
                    $order->load($i);
                    if ($order->canUnhold()) {
                        $order->rollbackStatus();
                        $count ++;
                    }
                }
                $this->commit();
                $result['message'][] = ['message' => $this->translate('%d order(s) has been unholded.', [count((array) $id)]), 'level' => 'success'];
                return $this->response($result, $this->getRequest()->getHeader('HTTP_REFERER'), 'retailer');
            } catch (Exception $e) {
                $this->rollback();
                $this->getContainer()->get('log')->logException($e);
                $result['error'] = 1;
                $result['message'][] = ['message' => $this->translate('An error detected while unholding orders.'), 'level' => 'danger'];
            }
        }
        return $this->redirectReferer('retailer/transaction/product/');
    }

    public function statusAction()
    {
        return $this->doSave('\\Seahinet\\Sales\\Model\\Order\\Status\\History', $this->getRequest()->getHeader('HTTP_REFERER'), [], function($model, $data) {
                    $order = new Model;
                    $order->load($data['id']);
                    $collection = $order->getStatus()->getPhase()->getStatus();
                    $flag = false;
                    foreach ($collection as $status) {
                        if ($status['id'] === $data['status_id']) {
                            $flag = $status['name'];
                            break;
                        }
                    }
                    if ($flag === false) {
                        throw new Exception('Invalid status.');
                    }
                    $order->setData('status_id', $data['status_id'])->save();
                    $model->setData([
                        'id' => null,
                        'admin_id' => null,
                        'order_id' => $data['id'],
                        'status' => $flag,
                        'is_customer_notified' => (int) isset($data['is_customer_notified']),
                        'is_visible_on_front' => (int) isset($data['is_visible_on_front'])
                    ]);
                }
        );
    }

    public function shipAction()
    {
        if ($id = $this->getRequest()->getQuery('id')) {
            $order = new Model;
            $order->load($id);
            if ($order->canShip()) {
                $root = $this->getLayout('retailer_sales_shipment_edit');
                return $root;
            }
        }
        return $this->notFoundAction();
    }

    public function invoiceAction()
    {
        if ($id = $this->getRequest()->getQuery('id')) {
            $order = new Model;
            $order->load($id);
            if ($order->canInvoice()) {
                $root = $this->getLayout('retailer_sales_invoice_edit');
                return $root;
            }
        }
        return $this->notFoundAction();
    }

    public function refundAction()
    {
        if ($id = $this->getRequest()->getQuery('id')) {
            $order = new Model;
            $order->load($id);
            if ($order->canRefund()) {
                $root = $this->getLayout('retailer_sales_creditmemo_edit2');
                //$root = $this->getLayout('retailer_sales_invoice_edit');
                return $root;
            }
        }
        return $this->notFoundAction();
    }

    public function saveAddressAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $result = $this->validateForm($data, ['order_id', 'id', 'is_billing']);
            if ($result['error'] === 0) {
                try {
                    $order = new Model;
                    $order->load($data['order_id']);
                    $address = new Address();
                    $address->load($data['id']);
                    $order->setData($data['is_billing'] ? 'billing_address' : 'shipping_address', $address->setData($data)->display(false))->save();
                    $result['reload'] = 1;
                    return $this->response($result, ':ADMIN/sales_order/view/?id=' . $data['order_id'], 'retailer');
                } catch (Exception $e) {
                    $this->getContainer()->get('log')->logException($e);
                    $result['error'] = 1;
                    $result['message'][] = ['message' => $this->translate('An error detected while saving.'), 'level' => 'danger'];
                }
            }
        }
        return $this->response($result ?? [], ':ADMIN/sales_order/');
    }

    public function saveDiscountAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $result = $this->validateForm($data, ['id', 'discount']);
            if ($result['error'] === 0) {
                try {
                    $order = new Model;
                    $order->load($data['id']);
                    if ($order->canCancel()) {
                        $currency = $order->getCurrency();
                        $discount = $currency->convert($data['discount']);
                        if ((float) $order->offsetGet('discount')) {
                            $detail = json_decode($order->offsetGet('discount_detail'), true);
                            $detail['Administrator'] = $data['discount'];
                            $baseDiscount = 0;
                            foreach ($detail as $price) {
                                $baseDiscount += $price;
                            }
                            $order->setData([
                                'base_discount' => $baseDiscount,
                                'discount' => $currency->convert($baseDiscount),
                                'discount_detail' => json_encode($detail)
                            ]);
                        } else {
                            $order->setData([
                                'base_discount' => $data['discount'],
                                'discount' => $discount,
                                'discount_detail' => '{"Administrator":' . $data['discount'] . '}'
                            ]);
                        }
                        $order->collateTotals();
                        $result['reload'] = 1;
                        return $this->response($result, ':ADMIN/sales_order/view/?id=' . $data['id'], 'retailer');
                    }
                } catch (Exception $e) {
                    $this->getContainer()->get('log')->logException($e);
                    $result['error'] = 1;
                    $result['message'][] = ['message' => $this->translate('An error detected while saving.'), 'level' => 'danger'];
                }
            }
        }
        return $this->response($result ?? [], ':ADMIN/sales_order/');
    }

    public function printAction()
    {
        if ($id = $this->getRequest()->getQuery('id')) {
            define('K_TCPDF_EXTERNAL_CONFIG', true);
            define('K_TCPDF_CALLS_IN_HTML', true);
            $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
            $root = $this->getLayout('admin_sales_order_print');
            $root->getChild('main', true)->setVariable('pdf', $pdf);
            $pdf->SetTitle($this->translate('Type Infomation'));
            $pdf->SetMargins(15, 27, 15);
            $pdf->setImageScale(1.25);
            $pdf->AddPage();
            $pdf->writeHTML($root->__toString(), true, false, true, false, '');
            $pdf->Output('order-' . $id, 'I');
        }
    }

}
