<?php

namespace Seahinet\Admin\Controller\Sales;

use Exception;
use Seahinet\Lib\Controller\AuthActionController;
use Seahinet\Sales\Model\Collection\Order\Status as StatusCollection;
use Seahinet\Sales\Model\Invoice;
use Seahinet\Sales\Model\Invoice\Item;
use Seahinet\Sales\Model\Order;
use Seahinet\Sales\Model\Order\Status\History;

class InvoiceController extends AuthActionController
{

    public function indexAction()
    {
        $root = $this->getLayout('admin_sales_invoice_list');
        return $root;
    }

    public function editAction()
    {
        $root = $this->getLayout('admin_sales_invoice_edit');
        if ($id = $this->getRequest()->getQuery('id')) {
            $model = new Model;
            $model->load($id);
            $root->getChild('edit', true)->setVariable('model', $model);
            $root->getChild('head')->setTitle('Edit Invoice / CMS');
        } else {
            $root->getChild('head')->setTitle('Add New Invoice / CMS');
        }
        return $root;
    }

    public function saveAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $result = $this->validateForm($data, ['order_id', 'item_id', 'qty']);
            try {
                $order = new Order;
                $order->load($data['order_id']);
                if (!$order->canInvoice()) {
                    return $this->notFoundAction();
                }
                $invoice = new Invoice;
                $invoice->setData($order->toArray())->setData([
                    'increment_id' => '',
                    'order_id' => $data['order_id'],
                    'comment' => isset($data['comment']) ? $data['comment'] : ''
                ]);
                if (!isset($data['include_shipping']) || !$data['include_shipping']) {
                    $invoice->setData([
                        'base_shipping' => 0,
                        'shipping' => 0
                    ]);
                }
                if (!isset($data['include_tax']) || !$data['include_tax']) {
                    $invoice->setData([
                        'base_tax' => 0,
                        'tax' => 0
                    ]);
                }
                $invoice->setId(null)->save();
                foreach ($order->getItems(true) as $item) {
                    foreach ($data['item_id'] as $key => $id) {
                        if ($id == $item->getId()) {
                            $obj = new Item($item->toArray());
                            $obj->setData([
                                'id' => null,
                                'item_id' => $item->getId(),
                                'invoice_id' => $invoice->getId(),
                                'qty' => $data['qty'][$key]
                            ])->collateTotals()->save();
                        }
                    }
                }
                $invoice->collateTotals()->save();
                $code = (int) $order->canShip() + (int) $order->canInvoice();
                if ($code) {
                    $code = $code === 2 ? 'complete' : 'processing';
                    $status = new StatusCollection;
                    $status->join('sales_order_phase', 'sales_order_phase.id=sales_order_status.phase_id', [])
                            ->where(['is_default' => 1, 'sales_order_phase.code' => $code])
                            ->limit(1);
                    $order->setData('status_id', $status[0]->getId())->save();
                    $history = new History;
                    $history->setData([
                        'admin_id' => (new Segment('admin'))->get('user')->getId(),
                        'order_id' => $this->getId(),
                        'status_id' => $status[0]->getId(),
                        'status' => $status[0]->offsetGet('name')
                    ])->save();
                }
            } catch (Exception $e) {
                $this->getContainer()->get('log')->logException($e);
                $result['message'][] = ['message' => $this->translate('An error detected while saving. Please check the log report or try again.'), 'level' => 'danger'];
                $result['error'] = 1;
            }
            return $this->response($result, ':ADMIN/sales_order/view/?id=' . $data['order_id']);
        }
        return $this->notFoundAction();
    }

}
