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
        error_reporting(E_ALL & ~E_NOTICE);
        $order = (new Model)->load($id);
        $currency = $order->getCurrency();
        $billing_address = nl2br($order->offsetGet('billing_address'));
        $shipping_address = nl2br($order->offsetGet('shipping_address'));
        $product = '';
        $num = 0;
        foreach ($this->getCollection() as $key=>$item){
            $options = json_decode($item['options'], true);
            if ($item['product_id'] && count($options)) {
                $option_str = '';
                foreach ($item['product']->getOptions() as $option) {
                    if (isset($options[$option->getId()])) {
                        $option_str .= $option_str !== '' ? '<br/>' : '';
                        $option_str .= $option['title']. ': '. (in_array($option['input'], ['select', 'radio', 'checkbox', 'multiselect']) ?
                            $option->getValue($options[$option->getId()]) : $options[$option->getId()]);
                    }
                }
            }
            $product .= ($num%2 == 0) ? '<tr class="background">' : '<tr>';
            $product .= '
                 <td align="center">'.$item['product_name'].'</td>
                 <td align="center">'.$item['sku'].'</td>
                 <td align="center">'.$option_str.'</td>
                 <td align="center">'.$currency->format($item['price']).'</td>
                 <td align="center">'.$item['qty'].'</td>
                 <td align="center">'.$currency->format($item['total']).'</td></tr>
                ';
            $num++;
        }
        $barcodeobj = new TCPDFBarcode('http://www.tcpdf.org', 'C128');
        $barcode = $barcodeobj->getBarcodeHTML(2, 30, 'black');
        $image_file = $this->getPubUrl('frontend/images/logo.png');
        $data['html'] = '<img src="'.$image_file.'">';
        $params = $pdf->serializeTCPDFtagParameters(array($order['increment_id'], 'C39', '115', '23', 80, 25, 0.4, array('position'=>'S', 'border'=>false, 'padding'=>4, 'fgcolor'=>array(0,0,0), 'bgcolor'=>array(255,255,255), 'text'=>true, 'font'=>'helvetica', 'fontsize'=>8, 'stretchtext'=>4), 'N'));
        $data['html'] .= '<tcpdf method="write1DBarcode" params="'.$params.'" />';
        $data['html'] .= '
        <style>
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
        </style>
        <table class="first" cellpadding="4" cellspacing="0">
         <tr class="background">
          <td class="head" colspan="2" align="center"><b>'.$this->translate('Order Infomation', [], 'sales').'</b></td>
          <td class="spacing" rowspan="12" align="center"></td>
          <td class="head" colspan="2" align="center"><b>'.$this->translate('Customer Infomation', [], 'sales').'</b></td>
         </tr>
         <tr>
          <td class="title" align="center"><b>'.$this->translate('ID').'</b></td><td class="content">'.$order['increment_id'].'</td>
          <td class="title" align="center"><b>'.($customer = $this->getCustomer()?$this->translate('ID') :'').'</b></td><td class="content">'.($customer = $this->getCustomer()?$this->getCustomer()->getId() :'').'</td>
         </tr>
         <tr class="background">
          <td class="title" align="center"><b>'.$this->translate('Status').'</b></td><td class="content">'.$this->translate($this->getStatus()->offsetGet('name'), [], 'sales').'</td>
          <td class="title" align="center"><b>'.($customer = $this->getCustomer()?$this->translate('Username'):'').'</b></td><td class="content">'.($customer = $this->getCustomer()?$this->getCustomer()['username']:'').'</td>
         </tr>
         <tr>
          <td class="title" align="center"><b>'.($store = $order->getStore()?$this->translate('Store') :'').'</b></td><td class="content">'.($storeName = $order->getStore()?$order->getStore()->offsetGet('name'):'').'</td>
          <td class="title" align="center"><b></b></td><td class="content"></td>
         </tr>
         <tr class="background">
          <td class="title" align="center"><b>'.($language = $order->getLanguage()?$this->translate('Language'):'').'</b></td><td class="content" >'.($language = $order->getLanguage()?$order->getLanguage()->offsetGet('name'):'').'</td>
          <td class="title" align="center"><b></b></td><td class="content"></td>
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
         <tr class="background">
          <td class="head" colspan="2" align="center"><b>'.$this->translate('Shipping Method', [], 'sales').'</b></td>
          <td class="head" colspan="2" align="center"><b>'.$this->translate('Payment Method', [], 'sales').'</b></td>
         </tr>
         <tr>
          <td class="colspan" colspan="2" align="center">'.$this->translate($order->getShippingMethod()->getLabel()).'</td>
          <td class="colspan" colspan="2" align="center">'.$this->translate($order->getPaymentMethod()->getLabel()).'</td>
         </tr>
         <tr class="background">
          <td class="colspan" colspan="2" align="center"></td>
          <td class="colspan" colspan="2" align="center">'.$this->translate('Order was placed using %s', [$order->offsetGet('currency')], 'sales').'</td>
         </tr>
         <tr>
          <td colspan="4" width="640px"></td>
         </tr>
         <tr>
          <td class="head" colspan="4" width="640px" align="center"><b>'.$this->translate('Product(s)').'</b></td>
         </tr>
         <tr>
           <td class="product-name" align="center"><b>'.$this->translate("Product Name", [], "sales").'</b></td>
           <td class="product-sku" align="center"><b>'.$this->translate("SKU", [], "sales").'</b></td>
           <td class="product-options" align="center"><b>'.$this->translate("Options", [], "sales").'</b></td>
           <td class="product-price" align="center"><b>'.$this->translate("Price", [], "sales").'</b></td>
           <td class="product-qty" align="center"><b>'.$this->translate("Qty", [], "sales").'</b></td>
           <td class="product-total" align="center"><b>'.$this->translate("Total", [], "sales").'</b></td>
         </tr>'.$product.'
         <tr>
          <td colspan="4" width="640px"></td>
         </tr>
         <tr class="background">
          <td class="head_line" colspan="4" width="640px" align="center"><b>'.$this->translate('Order Totals', [], 'sales').'</b></td>
         </tr>
         <tr>
          <td colspan="4" width="320px" align="center"><b>'.$this->translate('Subtotal', [], 'sales').'</b></td>
          <td colspan="4" width="320px">'.$currency->format($order->offsetGet('subtotal')).'</td>
         </tr>
         <tr class="background">
          <td colspan="4" width="320px" align="center"><b>'.$this->translate('Shipping &amp; Handling', [], 'sales').'</b></td>
          <td colspan="4" width="320px">'.$currency->format($order->offsetGet('shipping')).'</td>
         </tr>
         <tr>
          <td colspan="4" width="320px" align="center"><b>'.$this->translate('Tax', [], 'sales').'</b></td>
          <td colspan="4" width="320px" >'.$currency->format($order->offsetGet('tax')).'</td>
         </tr>
         <tr class="background">
          <td colspan="4" width="320px" align="center"><b>'.$this->translate('Discount', [], 'sales').'</b></td>
          <td colspan="4" width="320px">'.$currency->format($order->offsetGet('discount')).'</td>
         </tr>
         <tr>
          <td colspan="4" width="320px" align="center"><b>'.$this->translate('Grand Total', [], 'sales').'</b></td>
          <td colspan="4" width="320px">'.$currency->format($order->offsetGet('total')).'</td>
         </tr>
         <tr class="background">
          <td colspan="4" width="320px" align="center"><b>'.$this->translate('Total Paid', [], 'sales').'</b></td>
          <td colspan="4" width="320px">'.$currency->format($order->offsetGet('total_paid')).'</td>
         </tr>
         <tr>
          <td colspan="4" width="320px" align="center"><b>'.$this->translate('Total Refunded', [], 'sales').'</b></td>
          <td colspan="4" width="320px">'.$currency->format($order->offsetGet('total_refunded')).'</td>
         </tr>
        </table>
        ';
        $data['pdf_name'] = $this->translate('Order Infomation', [], 'sales').'.pdf';
        return $data;
    }
}
