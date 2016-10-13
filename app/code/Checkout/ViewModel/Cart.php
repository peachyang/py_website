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
        $items = $this->getCart()->getItems();
        $result = [];
        foreach ($items as $item) {
            $result[] = $item;
        }
        usort($result, function($a, $b) {
            return $a['store_id'] <=> $b['store_id'];
        });
        return $result;
    }

    public function getRow($item)
    {
        $row = $this->getChild('item');
        $row->setVariable('item', $item);
        return $row;
    }

}
