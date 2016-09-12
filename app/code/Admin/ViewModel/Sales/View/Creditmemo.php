<?php

namespace Seahinet\Admin\ViewModel\Sales\View;

use Seahinet\Lib\ViewModel\Template;
use Seahinet\Sales\Model\Creditmemo as Model;
use Seahinet\Customer\Model\Customer;
use Seahinet\Sales\Model\Order;
use TCPDFBarcode;
use Pelago\Emogrifier;

class Creditmemo extends Template
{

    protected $creditmemo = null;
    protected $order = null;
    protected $status = null;
    protected $phase = null;

    public function getCreditMemo()
    {
        if (is_null($this->creditmemo)) {
            $this->creditmemo = (new Model)->load($this->getQuery('id'));
        }
        return $this->creditmemo;
    }

    public function getOrder()
    {
        if (is_null($this->creditmemo)){
            $creditMemo = $this->getCreditMemo();
        }
        if (is_null($this->order)) {
            $this->order = (new Order)->load($this->creditmemo['order_id']);
        }
        return $this->order;
    }

    public function getCustomer()
    {
        if ($id = $this->getOrder()->offsetGet('customer_id')) {
            $customer = new Customer;
            $customer->load($id);
            return $customer;
        }
        return null;
    }

    public function getCollection()
    {
        $collection = $this->getCreditMemo()->getItems();
        return $collection;
    }

    public function getOrderModel(){
        error_reporting(E_ALL & ~E_NOTICE);
        $id = $this->getRequest()->getQuery('id');
        $invoice = (new Model)->load($id);
        $order = (new Order())->load($invoice['order_id']);
        return $order;
    }
    

    public function getHtml($pdf,$id){
        error_reporting(E_ALL & ~E_NOTICE);
        $invoice = (new Model)->load($id);
        $order = (new Order())->load($invoice['order_id']);
        $currency = $order->getCurrency();
        $billing_address = nl2br($order->offsetGet('billing_address'));
        $shipping_address = nl2br($order->offsetGet('shipping_address'));
        $barcodeobj = new TCPDFBarcode('http://www.tcpdf.org', 'C128');
        $barcode = $barcodeobj->getBarcodeHTML(2, 30, 'black');
        $image_file = $this->getPubUrl('frontend/images/logo.png');
        $data['html'] = '<img src="'.$image_file.'">';
        $params = $pdf->serializeTCPDFtagParameters(array($invoice['increment_id'], 'C39', '115', '23', 80, 25, 0.4, array('position'=>'S', 'border'=>false, 'padding'=>4, 'fgcolor'=>array(0,0,0), 'bgcolor'=>array(255,255,255), 'text'=>true, 'font'=>'helvetica', 'fontsize'=>8, 'stretchtext'=>4), 'N'));
        $data['html'] .= '<tcpdf method="write1DBarcode" params="'.$params.'" />';
        $data['css'] = '
        	table{font-family:stsongstdlight;border: 1px solid #ddd;font-size:12px}
            td{border: 1px solid #ddd;}
            .head{width:298px;font-size:14px;background-color:#999;color:#fff}
            .head_line{font-size:14px;background-color:#999;color:#fff}
            .title{width:78px;}
            .content{width:220px}
            .background{background-color:#f5f8fd}
            .spacing{width:44px;background-color:#fff;}
        	.product-name{width:140px;}
            .product-sku{width:100px;}
            .product-options{width:100px;}
            .product-price{width:100px;}
            .product-qty{width:100px;}
            .product-total{width:100px;}
            .colspan{width:298px;}
            ';
        $data['html'] .= '
        <table class="first" cellpadding="4" cellspacing="0">
         <tr class="background">
          <td class="head" colspan="2" align="center"><b>'.$this->translate('Credit Memo Information', [], 'sales').'</b></td>
          <td class="spacing" rowspan="20" align="center"></td>
          <td class="head" colspan="2" align="center"><b>'.$this->translate('Customer Information', [], 'sales').'</b></td>
         </tr>
         <tr>
          <td class="title" align="center"><b>'.$this->translate('ID').'</b></td><td class="content">'.$invoice['increment_id'].'</td>
          <td class="title" align="center"><b>'.$this->translate('ID').'</b></td><td class="content">'.$this->getCustomer()->getId().'</td>
         </tr>
         <tr class="background">
          <td class="title" align="center"><b>'.$this->translate('Shipping &amp; Handling', [], 'sales').'</b></td><td class="content">'.$currency->format($order->offsetGet('shipping')).'</td>
          <td class="title" align="center"><b>'.($customer = $this->getCustomer()?$this->translate('Username'):'').'</b></td><td class="content">'.($customer = $this->getCustomer()?$this->getCustomer()['username']:'').'</td>
         </tr>
         <tr>
          <td class="title" align="center"><b>'.$this->translate('Tax', [], 'sales').'</b></td><td class="content">'.$currency->format($order->offsetGet('tax')).'</td>
          <td class="title" align="center"><b></b></td><td class="content"></td>
         </tr>
         <tr class="background">
          <td class="title" align="center"><b>'.$this->translate('Discount', [], 'sales').'</b></td><td class="content" >'.$currency->format($order->offsetGet('discount')).'</td>
          <td class="title" align="center"><b></b></td><td class="content"></td>
         </tr>
         <tr>
          <td class="title" align="center"><b>'.$this->translate('Total').'</b></td><td class="content" >'.$currency->format($order->offsetGet('base_total')).'</td>
          <td class="title" align="center"><b></b></td><td class="content"></td>
         </tr>
         <tr class="background">
          <td class="title" align="center"><b>'.$this->translate('Total Paid', [], 'sales').'</b></td><td class="content" >'.$currency->format($order->offsetGet('total_paid')).'</td>
          <td class="title" align="center"><b></b></td><td class="content"></td>
         </tr>
         <tr>
          <td class="title" align="center"><b>'.$this->translate('Total Refunded', [], 'sales').'</b></td><td class="content" >'.$currency->format($order->offsetGet('total_refunded')).'</td>
          <td class="title" align="center"><b></b></td><td class="content"></td>
         </tr>
         <tr>
          <td colspan="4" width="640px"></td>
         </tr>
         <tr>
          <td class="head" colspan="2" align="center"><b>'.$this->translate('Order Information', [], 'sales').'</b></td>
          <td class="head" colspan="2" align="center"><b>'.$this->translate('Payment Method', [], 'sales').'</b></td>
         </tr>
         <tr>
          <td class="title" align="center"><b>'.$this->translate('ID').'</b></td><td class="content">'.$order['increment_id'].'</td>
          <td class="colspan" colspan="2" align="center">'.$this->translate($order->getPaymentMethod()->getLabel()).'</td>
         </tr>
         <tr class="background">
          <td class="title" align="center"><b>'.$this->translate('Status').'</b></td><td class="content">'.$this->translate($order->getStatus()->offsetGet('name'), [], 'sales').'</td>
          <td class="colspan" colspan="2" align="center">'.$this->translate('Order was placed using %s', [$order->offsetGet('currency')], 'sales').'</td>
         </tr>
         <tr>
          <td class="title" align="center"><b>'.($store = $order->getStore()?$this->translate('Store') :'').'</b></td><td class="content">'.($storeName = $order->getStore()?$order->getStore()->offsetGet('name'):'').'</td>
          <td class="colspan" colspan="2" align="center"></td>
         </tr>
         <tr class="background">
          <td class="title" align="center"><b>'.($language = $order->getLanguage()?$this->translate('Language'):'').'</b></td><td class="content" >'.($language = $order->getLanguage()?$order->getLanguage()->offsetGet('name'):'').'</td>
          <td class="colspan" colspan="2" align="center"></td>
         </tr>
         
         <tr>
          <td colspan="4" width="640px"></td>
         </tr>
         <tr class="background">
          <td class="head" colspan="2" align="center"><b>'.$this->translate('Billing Address', [], 'sales').'</b></td>
          <td class="head" colspan="2" align="center"><b>'.$this->translate('Shipping Address', [], 'sales').'</b></td>
         </tr>
         <tr>
          <td class="content-address" colspan="2">'.$billing_address.'</td>
          <td class="content-address" colspan="2">'.$shipping_address.'</td>
         </tr>
         <tr>
          <td colspan="4" width="640px"></td>
         </tr>
         <tr>
          <td class="head" colspan="2" align="center"><b>'.$this->translate('Shipping Method', [], 'sales').'</b></td>
          <td class="head" colspan="2" align="center"><b>'.$this->translate('Comment').'</b></td>
         </tr>
         <tr>
          <td class="colspan" colspan="2" align="center">'.$this->translate($order->getShippingMethod()->getLabel()).'</td>
          <td class="colspan" colspan="2" align="center">'.$invoice['comment'].'</td>
         </tr>
        </table>
        ';
        $data['html'] = $data['css'] ? (new Emogrifier($data['html'],$data['css']))->emogrifyBodyContent() : $data['html'];
        $data['pdf_name'] = $this->translate('Shipment Information', [], 'sales').'.pdf';
        return $data;
    }

}
