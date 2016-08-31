<?php

namespace Seahinet\Shipping\Model;

class FlatRate extends AbstractMethod
{

    const METHOD_CODE = 'flat_rate';

    public function getShippingRate($items)
    {
        $config = $this->getContainer()->get('config');
        if ($config['shipping/' . self::METHOD_CODE . '/unit']) {
            $total = 0;
            foreach ($items as $item) {
                if (!$item['free_shipping'] && !$item['is_virtual']) {
                    $total += $item['base_price'] * $item['qty'];
                }
            }
            return $config['shipping/' . self::METHOD_CODE . '/calc'] ?
                    $total * $config['shipping/' . self::METHOD_CODE . '/rate'] :
                    $config['shipping/' . self::METHOD_CODE . '/rate'];
        } else {
            $result = 0;
            $calc = $config['shipping/' . self::METHOD_CODE . '/calc'];
            $rate = $config['shipping/' . self::METHOD_CODE . '/rate'];
            foreach ($items as $item) {
                if (!$item['free_shipping'] && !$item['is_virtual']) {
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
