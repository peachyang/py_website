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

    public function getQty($withDisabled = false)
    {
        if (is_null(self::$qty)) {
            self::$qty = $this->getCart()->getQty(null, $withDisabled);
        }
        return self::$qty;
    }

    public function getItems()
    {
        $items = $this->getCart()->getItems();
        $result = [];
        foreach ($items as $item) {
            $options = json_decode($item['options'], true);
            foreach ($item['product']->getOptions() as $option) {
                if ($option['is_required'] && !isset($options[$option->getId()])) {
                    $item['disabled'] = true;
                }
            }
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
