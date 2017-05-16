<?php

namespace Seahinet\Sales\Source;

use Seahinet\Lib\Source\SourceInterface;
use Seahinet\Sales\Model\Cart;
use Seahinet\Shipping\Model\AbstractMethod;

class ShippingMethod implements SourceInterface
{

    use \Seahinet\Lib\Traits\Container;

    public function getSourceArray($storeId = null)
    {
        $config = $this->getContainer()->get('config');
        $address = Cart::instance()->getShippingAddress();
        if ($storeId) {
            $items = Cart::instance()->getItems();
            $total = 0;
            foreach ($items as $item) {
                if ($item['store_id'] == $storeId) {
                    $total += $item['base_total'];
                }
            }
        } else {
            $total = Cart::instance()->offsetGet('base_price');
        }
        $result = [];
        foreach ($config['system']['shipping']['children'] as $code => $info) {
            $className = $config['shipping/' . $code . '/model'];
            $country = $config['shipping/' . $code . '/country'];
            $model = new $className;
            if ($model instanceof AbstractMethod && $model->available(['total' => $total]) &&
                    (!$address || !$country || in_array($address->offsetGet('country'), explode(',', $country)))) {
                $result[$code] = $config['shipping/' . $code . '/label'];
            }
        }
        return $result;
    }

}
