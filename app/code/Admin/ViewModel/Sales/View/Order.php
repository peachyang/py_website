<?php

namespace Seahinet\Admin\ViewModel\Sales\View;

use Seahinet\Customer\Model\Customer;
use Seahinet\Lib\ViewModel\Template;
use Seahinet\Sales\Model\Order as Model;
use TCPDF;
use TCPDFBarcode;

class Order extends Template
{

    protected $order = null;
    protected $status = null;
    protected $phase = null;

    public function getOrder()
    {
        if (is_null($this->order)) {
            $this->order = (new Model)->load($this->getQuery('id'));
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
        $collection = $this->getOrder()->getItems();
        return $collection;
    }

    public function getStatus()
    {
        if (is_null($this->status)) {
            $this->status = $this->getOrder()->getStatus();
        }
        return $this->status;
    }

    public function getPhase()
    {
        if (is_null($this->phase)) {
            $this->phase = $this->getStatus()->getPhase();
        }
        return $this->phase;
    }

    public function getHtml($pdf,$id){
        $order = (new Model)->load($id);
        $currency = $order->getCurrency();
        //var_dump($order);die;
        $barcodeobj = new TCPDFBarcode('http://www.tcpdf.org', 'C128');
        $barcode = $barcodeobj->getBarcodeHTML(2, 30, 'black');
        $image_file = BP.'pub\theme\default\frontend\images\logo-o.png';
        $data['html'] = '<img src="'.$image_file.'">';
        $params = $pdf->serializeTCPDFtagParameters(array('1216080907337389', 'C39', '115', '20', 80, 30, 0.4, array('position'=>'S', 'border'=>false, 'padding'=>4, 'fgcolor'=>array(0,0,0), 'bgcolor'=>array(255,255,255), 'text'=>true, 'font'=>'helvetica', 'fontsize'=>8, 'stretchtext'=>4), 'N'));
        $data['html'] .= '<tcpdf method="write1DBarcode" params="'.$params.'" />';
        $data['html'] .= '
        <style>
        	table{font-family:stsongstdlight;border: 1px solid #ddd;}
            td{border: 1px solid #ddd}
            .head{width:298px;font-size:18px;background-color:#999;color:#fff}
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
        </style>
        <table class="first" cellpadding="4" cellspacing="0">
         <tr class="background">
          <td class="head" colspan="2" align="center"><b>'
          .$this->translate('Order Infomation', [], 'sales').
          '</b></td>
          <td class="spacing" rowspan="17" align="center"></td>
          <td class="head" colspan="2" align="center"><b>'
          .$this->translate('Customer Infomation', [], 'sales').
          '</b></td>
         </tr>
         <tr>
          <td class="title" align="center"><b>ID</b></td><td class="content" align="center">1216080907337389</td>
          <td class="title" align="center"><b>ID</b></td><td class="content" align="center">1</td>
         </tr>
         <tr class="background">
          <td class="title" align="center"><b>状态</b></td><td class="content" align="center">等待处理</td>
          <td class="title" align="center"><b>用户名</b></td><td class="content" align="center">admin</td>
         </tr>
         <tr>
          <td class="title" align="center"><b>店铺</b></td><td class="content" align="center">Default</td>
          <td class="title" align="center"><b></b></td><td class="content" align="center"></td>
         </tr>
         <tr class="background">
          <td class="title" align="center"><b>语言</b></td><td class="content" align="center">中文 (中国)</td>
          <td class="title" align="center"><b></b></td><td class="content" align="center"></td>
         </tr>
         <tr>
          <td colspan="4" width="640px">&nbsp;<br>&nbsp;</td>
         </tr>
         <tr class="background">
          <td class="head" colspan="2" align="center"><b>'
          .$this->translate('Billing Address', [], 'sales').
          '</b></td>
          <td class="head" colspan="2" align="center"><b>'
          .$this->translate('Shipping Address', [], 'sales').
          '</b></td>
         </tr>
         <tr>
          <td class="title" align="center"><b>名称</b></td><td class="content" align="center">mingcheng12212121608090733738912160809073373891216080907337389</td>
          <td class="title" align="center"><b>ID</b></td><td class="content" align="center">1216080907337389</td>
         </tr>
         <tr class="background">
          <td class="title" align="center"><b>电话号码</b></td><td class="content" align="center">15966913290</td>
          <td class="title" align="center"><b>状态</b></td><td class="content" align="center">等待处理2</td>
         </tr>
         <tr>
          <td class="title" align="center"><b>地址</b></td><td class="content" align="center">山东省青岛市市南区1</td>
          <td class="title" align="center"><b>店铺</b></td><td class="content" align="center">Default2</td>
         </tr>
         <tr class="background">
          <td class="title" align="center"><b>邮编</b></td><td class="content" align="center">266100</td>
          <td class="title" align="center"><b></b></td><td class="content" align="center"></td>
         </tr>
         <tr>
          <td colspan="4" width="640px">&nbsp;<br>&nbsp;</td>
         </tr>
         <tr class="background">
          <td class="head" colspan="2" align="center"><b>'
          .$this->translate('Shipping Method', [], 'sales').
          '</b></td>
          <td class="head" colspan="2" align="center"><b>'
          .$this->translate('Payment Method', [], 'sales').
          '</b></td>
         </tr>
         <tr>
          <td class="title" align="center"><b>名称</b></td><td class="content" align="center">mingcheng12212</td>
          <td class="title" align="center"><b>ID</b></td><td class="content" align="center">1216080907337389</td>
         </tr>
         <tr class="background">
          <td class="title" align="center"><b>电话号码</b></td><td class="content" align="center">15966913290</td>
          <td class="title" align="center"><b>状态</b></td><td class="content" align="center">等待处理1</td>
         </tr>
         <tr>
          <td class="title" align="center"><b>地址</b></td><td class="content" align="center">山东省青岛市市南区</td>
          <td class="title" align="center"><b>店铺</b></td><td class="content" align="center">Default</td>
         </tr>
         <tr class="background">
          <td class="title" align="center"><b>邮编</b></td><td class="content" align="center">266100</td>
          <td class="title" align="center"><b></b></td><td class="content" align="center"></td>
         </tr>
         <tr>
          <td colspan="4" width="640px">&nbsp;<br>&nbsp;</td>
         </tr>
         <tr class="background">
           <td class="head product-name" align="center">'.$this->translate("Product Name", [], "sales").'</td>
           <td class="head product-sku" align="center">'.$this->translate("SKU", [], "sales").'</td>
           <td class="head product-options" align="center">'.$this->translate("Options", [], "sales").'</td>
           <td class="head product-price" align="center">'.$this->translate("Price", [], "sales").'</td>
           <td class="head product-qty" align="center">'.$this->translate("Qty", [], "sales").'</td>
           <td class="head product-total" align="center">'.$this->translate("Total", [], "sales").'</td>
         </tr>
         <tr>
           <td align="center">产品1</td>
           <td align="center">product</td>
           <td align="center">颜色: 白<br>尺寸: 180</td>
           <td align="center">￥95.00</td>
           <td align="center">1.0000</td>
           <td align="center">￥95.00</td>
         </tr>
        </table>
        ';
        $data['title'] = '订单信息';
        $data['pdf_name'] = '订单信息.pdf';
        return $data;
    }
}
