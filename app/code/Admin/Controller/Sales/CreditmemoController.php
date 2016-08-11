<?php

namespace Seahinet\Admin\Controller\Sales;

use Exception;
use Seahinet\Lib\Controller\AuthActionController;
use Seahinet\Lib\Session\Segment;
use Seahinet\Sales\Model\Collection\Order\Status as StatusCollection;
use Seahinet\Sales\Model\CreditMemo;
use Seahinet\Sales\Model\CreditMemo\Item;
use Seahinet\Sales\Model\Order;
use Seahinet\Sales\Model\Order\Status\History;

class CreditmemoController extends AuthActionController
{

    use \Seahinet\Lib\Traits\DB;

    public function indexAction()
    {
        $root = $this->getLayout('admin_sales_creditmemo_list');
        return $root;
    }

    public function viewAction()
    {
        if ($id = $this->getRequest()->getQuery('id')) {
            return $this->getLayout('admin_sales_creditmemo_view');
        }
        return $this->notFoundAction();
    }

    public function deleteAction()
    {
        return $this->doDelete('\\Seahinet\\Sales\\Model\\CreditMemo', ':ADMIN/sales_creditmemo/');
    }

    public function saveAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $result = $this->validateForm($data, ['order_id', 'item_id', 'qty']);
            try {
                $order = new Order;
                $order->load($data['order_id']);
                if (!$order->canRefund()) {
                    return $this->redirectReferer(':ADMIN/sales_order/view/?id=' . $data['order_id']);
                }
                $memo = new CreditMemo;
                $memo->setData($order->toArray())->setData([
                    'increment_id' => '',
                    'order_id' => $data['order_id'],
                    'comment' => isset($data['comment']) ? $data['comment'] : ''
                ]);
                if (!isset($data['include_shipping']) || !$data['include_shipping']) {
                    $memo->setData([
                        'base_shipping' => 0,
                        'shipping' => 0
                    ]);
                }
                if (!isset($data['include_tax']) || !$data['include_tax']) {
                    $memo->setData([
                        'base_tax' => 0,
                        'tax' => 0
                    ]);
                }
                $this->beginTransaction();
                $memo->setId(null)->save();
                foreach ($order->getItems(true) as $item) {
                    foreach ($data['item_id'] as $key => $id) {
                        if ($id == $item->getId()) {
                            $obj = new Item($item->toArray());
                            $obj->setData([
                                'id' => null,
                                'item_id' => $item->getId(),
                                'creditmemo_id' => $memo->getId(),
                                'qty' => $data['qty'][$key]
                            ])->collateTotals()->save();
                        }
                    }
                }
                $memo->collateTotals()->save();
                $order->setData([
                    'base_total_refunded' => (float) $order['base_total_refunded'] + $memo['base_total'],
                    'total_refunded' => (float) $order['total_refunded'] + $memo['total']
                ]);
                if (!$order->canRefund()) {
                    $status = new StatusCollection;
                    $status->join('sales_order_phase', 'sales_order_phase.id=sales_order_status.phase_id', [])
                            ->where(['is_default' => 1, 'sales_order_phase.code' => 'closed'])
                            ->limit(1);
                    $order->setData('status_id', $status[0]->getId());
                    $history = new History;
                    $history->setData([
                        'admin_id' => (new Segment('admin'))->get('user')->getId(),
                        'order_id' => $order->getId(),
                        'status_id' => $status[0]->getId(),
                        'status' => $status[0]->offsetGet('name')
                    ])->save();
                }
                $order->save();
                $this->commit();
            } catch (Exception $e) {
                $this->rollback();
                $this->getContainer()->get('log')->logException($e);
                $result['message'][] = ['message' => $this->translate('An error detected while saving. Please check the log report or try again.'), 'level' => 'danger'];
                $result['error'] = 1;
            }
            return $this->response($result, ':ADMIN/sales_order/view/?id=' . $data['order_id']);
        }
        return $this->notFoundAction();
    }

}
