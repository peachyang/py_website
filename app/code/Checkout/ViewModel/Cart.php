<?php

namespace Seahinet\Checkout\ViewModel;

use Seahinet\Checkout\ViewModel\Cart\Item;
use Seahinet\I18n\Model\Currency;
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
        if (is_null(self::$currency)) {
            self::$currency = new Currency;
            self::$currency->load($this->getRequest()->getCookie('currency', $this->getConfig()['i18n/currency/base']), 'code');
        }
        return self::$currency;
    }

    public function getQty()
    {
        if (is_null(self::$qty)) {
            self::$qty = 0;
            foreach ($this->getItems() as $item) {
                self::$qty += $item['qty'];
            }
        }
        return self::$qty;
    }

    public function getItems()
    {
        return $this->getCart()->getItems();
    }

    public function getRow($item)
    {
        $row = new Item;
        $row->setTemplate('checkout/cart/item');
        $row->setVariable('item', $item);
        return $row;
    }

}
