<?php

namespace Seahinet\Checkout\ViewModel;

use Seahinet\Lib\ViewModel\Template;
use Seahinet\Sales\Model\Cart as CartSingleton;

class Cart extends Template
{

    protected static $cart = null;
    protected static $currency = null;
    protected static $qty = null;

    public function getCart()
    {
        if (is_null(self::$cart)) {
            self::$cart = CartSingleton::instance();
        }
        return self::$cart;
    }

    public function getCurrency()
    {
        return $this->getContainer()->get('currency');
    }

    public function getQty()
    {
        if (is_null(self::$qty)) {
            self::$qty = $this->getCart()->getQty();
        }
        return self::$qty;
    }

    public function getItems()
    {
        return $this->getCart()->getItems();
    }

    public function getRow($item, $rowspan = 0)
    {
        $row = $this->getChild('item');
        $row->setVariable('item', $item);
        return $row;
    }

    public function getLogView()
    {
        $cookies = $this->getRequest()->getCookie('log_view');
        $logView = [];
        foreach (explode(',',$cookies) as $item){
            if ($item){
                $logView[] = $this->getCart()->getLogView($item);
            }
        }
        return $logView;
    }

}