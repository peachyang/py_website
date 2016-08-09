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
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('Nicola Asuni');
            $pdf->SetTitle('Set Title');
            $pdf->SetSubject('TCPDF Tutorial');
            $pdf->SetKeywords('TCPDF, PDF, example, test, guide');
            //$pdf->SetHeaderData(BP.'pub/theme/default/frontend/images/logo-o.svg', PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 061', PDF_HEADER_STRING);
            $pdf->SetHeaderData(BP.'pub/theme/default/frontend/images/logo-o.svg', '', '');
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
                require_once(dirname(__FILE__).'/lang/eng.php');
                $pdf->setLanguageArray($l);
            }
            $pdf->SetFont('stsongstdlight', '', 10);
            $pdf->AddPage();
            header("Content-Type: text/html; charset=utf-8");
            $html = <<<EOD
            <div class="view clearfix">
            <div class="panel">
            <div class="panel-heading">
            <h4 class="panel-title">订单信息</h4>
            </div>
            <div class="panel-body">
                ID: 1216080907337389<br>
                                            状态: 等待处理<br>
                                            店铺: Default<br>
                                            语言: 中文 (中国)        </div>
            </div>
            <div class="panel">
            <div class="panel-heading">
            <h4 class="panel-title">顾客信息</h4>
            </div>
            <div class="panel-body">                ID: 1<br>
                                        用户名: admin            </div>
            </div>
            <div class="panel">
            <div class="panel-heading">
            <h4 class="panel-title">账单地址</h4>
            </div>
            <div class="panel-body">
                                        名称: 1<br>
                                        电话号码: 1<br>
                                        国家 : ???<br>
                                        省: 1<br>
                                        市: 1<br>
                                        县: ???        </div>
            </div>
            <div class="panel">
            <div class="panel-heading">
            <h4 class="panel-title">收货信息</h4>
            </div>
            <div class="panel-body">
                                            名称: 1<br>
                                            电话号码: 1<br>
                                            国家 : ???<br>
                                            省: 1<br>
                                            市: 1<br>
                                            县: ???        </div>
            </div>
            <div class="panel">
            <div class="panel-heading">
            <h4 class="panel-title">支付方式</h4>
            </div>
            <div class="panel-body">
            </div>
            </div>
            <div class="panel wide">
            <div class="panel-heading">
            <h4 class="panel-title">产品</h4>
            </div>
            <div class="panel-body grid table-responsive">
            <table class="table table-hover table-striped table-no-border">
            <thead class="sort-by">
            <tr>
            <th>产品名称</th>
            <th>SKU</th>
            <th>选项</th>
            <th>价格</th>
            <th>数量</th>
            <th>总价</th>
            </tr>
            </thead>
            <tbody>
            <tr data-id="1">
            <td>产品1</td>
            <td>product</td>
            <td>
                                         颜色: 白<br>尺寸: 180<br>                            </td>
            <td>￥95.00</td>
            <td>1.0000</td>
            <td>￥95.00</td>
            </tr>
            </tbody>
            </table>
            </div>
            </div>
            <div class="panel">
            <div class="panel-heading">
            <h4 class="panel-title">状态历史</h4>
            </div>
            <div class="panel-body">
            <form method="post" action="http://127.0.0.1/ecomv2/admin/sales_order/status/" novalidate="novalidate">
            <input type="hidden" value="1" name="id">
            <input type="hidden" value="6407717fe975946d3dd2329da4f9662eb767c5e3" name="csrf">
            <div class="input-box">
            <label class="control-label" for="status">状态</label>
            <select class="form-control" id="status" name="status_id">
            <option selected="selected" value="1">
                                            挂起                            </option>
            </select>
            </div>
            <div class="input-box">
            <label class="control-label" for="comment">备注</label>
            <textarea id="comment" name="comment" class="form-control"></textarea>
            </div>
            <div class="input-box">
            <input type="checkbox" value="1" id="notify-customer" name="is_customer_notified">
            <label for="notify-customer">通知顾客</label>
            </div>
            <div class="input-box">
            <input type="checkbox" value="1" id="visible" name="is_visible_on_front">
            <label for="visible">前端可见</label>
            </div>
            <div class="buttons-set">
            <button class="btn btn-submit" type="submit"><span>提交</span></button>
            </div>
            </form>
            <dl class="history">
            </dl>
            </div>
            </div>
            <div class="panel">
            <div class="panel-heading">
            <h4 class="panel-title">订单总价</h4>
            </div>
            <div class="panel-body">
            <dl class="dl-horizontal">
            <dt>小计</dt>
            <dd>￥95.00</dd>
            <dt>运费&amp;手续费</dt>
            <dd>￥0.00</dd>
            <dt>税费</dt>
            <dd>￥0.00</dd>
            <dt>总价</dt>
            <dd>￥95.00</dd>
            <dt>支付金额</dt>
            <dd>￥0.00</dd>
            <dt>退款金额</dt>
            <dd>￥0.00</dd>
            </dl>
            </div>
            </div>
            </div>
EOD;
            $pdf->writeHTML($html, true, false, true, false, '');
            $pdf->AddPage();
            $pdf->writeHTML($html, true, false, true, false, '');
            $pdf->lastPage();
            $pdf->Output('example_061.pdf', 'I');
        }
    }

}
