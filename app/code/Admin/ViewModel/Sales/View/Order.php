<?php

namespace Seahinet\Admin\ViewModel\Sales\View;

use Seahinet\Customer\Model\Customer;
use Seahinet\Lib\ViewModel\Template;
use Seahinet\Sales\Model\Order as Model;
use TCPDF;
use TCPDFBarcode;
use Pelago\Emogrifier;

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
        $barcodeobj->getBarcodeHTML(2, 30, 'black');
        $image_file = $this->getPubUrl('frontend/images/logo.png');
        
        $data['pdf_name'] = $this->translate('Order Infomation', [], 'sales').'.pdf';
        return $data;
    }
}
