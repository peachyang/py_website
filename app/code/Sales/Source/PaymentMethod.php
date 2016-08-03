<?php

namespace Seahinet\Sales\Source;

use Seahinet\Lib\Source\SourceInterface;
use Seahinet\Payment\Model\AbstractMethod;
use Seahinet\Payment\Model\Free;
use Seahinet\Sales\Model\Cart;

class PaymentMethod implements SourceInterface
{

    use \Seahinet\Lib\Traits\Container;

    public function getSourceArray($getObject = false)
    {
        $config = $this->getContainer()->get('config');
        $address = Cart::instance()->getShippingAddress();
        if ($total = (float) Cart::instance()->offsetGet('base_total')) {
            $result = [];
            foreach ($config['system']['payment']['children'] as $code => $info) {
                if ($code === 'payment_free') {
                    continue;
                }
                $className = $config['payment/' . $code . '/model'];
                $max = $config['payment/' . $code . '/max_total'];
                $country = $config['payment/' . $code . '/country'];
                $model = new $className;
                if ($model instanceof AbstractMethod && $model->available() &&
                        (!$address || !$country || in_array($address->offsetGet('country'), $country)) &&
                        $total >= $config['payment/' . $code . '/min_total'] &&
                        (!$max || $total <= $max)) {
                    $result[$code] = $getObject ? $model->setLabel($config['payment/' . $code . '/label']) : $config['payment/' . $code . '/label'];
                }
            }
            return $result;
        } else {
            return ['payment_free' => $getObject ? (new Free)->setLabel($config['payment/payment_free/label']) : $config['payment/payment_free/label']];
        }
    }

}
