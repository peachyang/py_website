<?php

namespace Seahinet\Shipping\Model;

use Seahinet\Sales\Model\Cart;

class FlatRate extends AbstractMethod
{

    const METHOD_CODE = 'flat_rate';

    public function getShippingRate($storeId)
    {
        $config = $this->getContainer()->get('config');
        $cart = Cart::instance();
        if ($config['shipping/' . self::METHOD_CODE . '/unit']) {
            return $config['shipping/' . self::METHOD_CODE . '/calc'] ?
                    $cart->offsetGet('base_total') * $config['shipping/' . self::METHOD_CODE . '/rate'] :
                    $config['shipping/' . self::METHOD_CODE . '/rate'];
        } else {
            $items = $cart->getItems(true);
            $result = 0;
            $calc = $config['shipping/' . self::METHOD_CODE . '/calc'];
            $rate = $config['shipping/' . self::METHOD_CODE . '/rate'];
            foreach ($items as $item) {
                if ($item->offsetGet('store_id') == $storeId) {
                    $result += ($calc ? $item->offsetGet('base_price') * $rate : $rate) * $item->offsetGet('qty');
                }
            }
            return $result;
        }
    }

    public function available()
    {
        return $this->getContainer()->get('config')['shipping/' . self::METHOD_CODE . '/enable'];
    }

}
