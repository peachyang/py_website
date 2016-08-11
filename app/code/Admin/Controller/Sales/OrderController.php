<?php

namespace Seahinet\Admin\Controller\Sales;

use Exception;
use Seahinet\Lib\Controller\AuthActionController;
use Seahinet\Lib\Session\Segment;
use Seahinet\Sales\Model\Collection\Order\Status;
use Seahinet\Sales\Model\Collection\Order\Status\History as HistoryCollection;
use Seahinet\Sales\Model\Order as Model;
use Seahinet\Sales\Model\Order\Status\History;
use TCPDF;
use TCPDFBarcode;
use Seahinet\Lib\Traits\Translate;
use Seahinet\Admin\ViewModel\Sales\View\Order as Pdf;

class OrderController extends AuthActionController
{

    use \Seahinet\Lib\Traits\DB;

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
                $userId = (new Segment('admin'))->get('user')->getId();
                $statusId = $status[0]->getId();
                $this->beginTransaction();
                foreach ((array) $id as $i) {
                    $order = new Model;
                    $order->load($i);
                    $history = new History;
                    $history->setData([
                        'admin_id' => $userId,
                        'order_id' => $i,
                        'status_id' => $statusId,
                        'status' => $status[0]->offsetGet('name')
                    ])->save();
                    if (in_array($order->getStatus()->getPhase()->offsetGet('code'), ['pending', 'pending_payment'])) {
                        $order->setData('status_id', $statusId)
                                ->save();
                        $count ++;
                    }
                }
                $this->commit();
                $result['message'][] = ['message' => $this->translate('%d order(s) has been canceled.', [count((array) $id)]), 'level' => 'success'];
                return $this->response($result, $this->getRequest()->getHeader('HTTP_REFERER'));
            } catch (Exception $e) {
                $this->rollback();
                $this->getContainer()->get('log')->logException($e);
                $result['error'] = 1;
                $result['message'][] = ['message' => $this->translate('An error detected while canceling orders.'), 'level' => 'danger'];
            }
        }
        return $this->redirectReferer(':ADMIN/sales_order/');
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
                $userId = (new Segment('admin'))->get('user')->getId();
                $statusId = $status[0]->getId();
                $this->beginTransaction();
                foreach ((array) $id as $i) {
                    $order = new Model;
                    $order->load($i);
                    $history = new History;
                    $history->setData([
                        'admin_id' => $userId,
                        'order_id' => $i,
                        'status_id' => $statusId,
                        'status' => $status[0]->offsetGet('name')
                    ])->save();
                    if ($order->getStatus()->getPhase()->offsetGet('code') === 'processing') {
                        $order->setData('status_id', $statusId)
                                ->save();
                        $count ++;
                    }
                }
                $this->commit();
                $result['message'][] = ['message' => $this->translate('%d order(s) has been holded.', [count((array) $id)]), 'level' => 'success'];
                return $this->response($result, $this->getRequest()->getHeader('HTTP_REFERER'));
            } catch (Exception $e) {
                $this->rollback();
                $this->getContainer()->get('log')->logException($e);
                $result['error'] = 1;
                $result['message'][] = ['message' => $this->translate('An error detected while holding orders.'), 'level' => 'danger'];
            }
        }
        return $this->redirectReferer(':ADMIN/sales_order/');
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
                $userId = (new Segment('admin'))->get('user')->getId();
                $this->beginTransaction();
                foreach ((array) $id as $i) {
                    $order = new Model;
                    $order->load($i);
                    $history = new HistoryCollection;
                    $history->join('sales_order_status', 'sales_order_status.id=sales_order_status_history.status_id', ['name'])
                            ->join('sales_order_phase', 'sales_order_phase.id=sales_order_status.phase_id', [])
                            ->where(['order_id' => $i])
                            ->order('created_at DESC')
                            ->limit(1)
                    ->where->notEqualTo('sales_order_phase.code', 'holded');
                    if ($order->getStatus()->getPhase()->offsetGet('code') === 'holded') {
                        if (count($history)) {
                            $statusId = $history[0]->offsetGet('status_id');
                            $statusName = $history[0]->offsetGet('name');
                        } else {
                            $statusId = $status->offsetGet('id');
                            $statusName = $status->offsetGet('name');
                        }
                        $order->setData('status_id', $statusId)->save();
                        $history = new History;
                        $history->setData([
                            'admin_id' => $userId,
                            'order_id' => $i,
                            'status_id' => $statusId,
                            'status' => $statusName
                        ])->save();
                        $count ++;
                    }
                }
                $this->commit();
                $result['message'][] = ['message' => $this->translate('%d order(s) has been unholded.', [count((array) $id)]), 'level' => 'success'];
                return $this->response($result, $this->getRequest()->getHeader('HTTP_REFERER'));
            } catch (Exception $e) {
                $this->rollback();
                $this->getContainer()->get('log')->logException($e);
                $result['error'] = 1;
                $result['message'][] = ['message' => $this->translate('An error detected while unholding orders.'), 'level' => 'danger'];
            }
        }
        return $this->redirectReferer(':ADMIN/sales_order/');
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
                    $user = (new Segment('admin'))->get('user');
                    $model->setData([
                        'id' => null,
                        'admin_id' => $user->getId(),
                        'order_id' => $data['id'],
                        'status' => $flag,
                        'is_customer_notified' => isset($data['is_customer_notified']) ? 1 : 0,
                        'is_visible_on_front' => isset($data['is_visible_on_front']) ? 1 : 0
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
                $root = $this->getLayout('admin_sales_shipment_edit');
                $root->getChild('breadcrumb', true)->addCrumb([
                    'link' => ':ADMIN/sales_order/view/?id=' . $id,
                    'label' => 'Order'
                ])->addCrumb([
                    'link' => ':ADMIN/sales_order/ship/?id=' . $id,
                    'label' => 'Shipment'
                ]);
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
                $root = $this->getLayout('admin_sales_invoice_edit');
                $root->getChild('breadcrumb', true)->addCrumb([
                    'link' => ':ADMIN/sales_order/view/?id=' . $id,
                    'label' => 'Order'
                ])->addCrumb([
                    'link' => ':ADMIN/sales_order/invoice/?id=' . $id,
                    'label' => 'Invoice'
                ]);
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
                $root = $this->getLayout('admin_sales_creditmemo_edit');
                $root->getChild('breadcrumb', true)->addCrumb([
                    'link' => ':ADMIN/sales_order/view/?id=' . $id,
                    'label' => 'Order'
                ])->addCrumb([
                    'link' => ':ADMIN/sales_order/refund/?id=' . $id,
                    'label' => 'Credit Memo'
                ]);
                return $root;
            }
        }
        return $this->notFoundAction();
    }
    
    public function printAction(){
        if ($id = $this->getRequest()->getQuery('id')) {
            require_once(BP.'vendor\tecnickcom\tcpdf\examples\tcpdf_include.php');
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            $data = (new Pdf)->getHtml($pdf,$id);
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('Nicola Asuni');
            $pdf->SetTitle($this->translate('Type Infomation'));
            $pdf->SetSubject('TCPDF Tutorial');
            $pdf->SetKeywords('TCPDF, PDF, example, test, guide');
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            $pdf->SetFont('stsongstdlight', '', 10);
            $pdf->AddPage();
            $pdf->writeHTML($data['html'], true, false, true, false, '');
            $pdf->lastPage();
            $pdf->Output($data['pdf_name'], 'I');
        }
    }
    
}
