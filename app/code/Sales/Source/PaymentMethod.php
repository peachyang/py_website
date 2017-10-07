<?php

namespace Seahinet\Sales\Source;

use Seahinet\I18n\Model\Locate;
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
            $countryCode = $address ? (new Locate)->getCode('country', $address->offsetGet('country')) : '';
            foreach ($config['system']['payment']['children'] as $code => $info) {
                if ($code === 'payment_free') {
                    continue;
                }
                $className = $config['payment/' . $code . '/model'];
                $country = $config['payment/' . $code . '/country'];
                $model = new $className;
                if ($model instanceof AbstractMethod && $model->available(['total' => $total]) === true &&
                        (!$countryCode || !$country || in_array($countryCode, explode(',', $country)))) {
                    $result[$code] = $getObject ? $model : $config['payment/' . $code . '/label'];
                }
            }
            return $result;
        } else {
            return ['payment_free' => $getObject ? (new Free) : $config['payment/payment_free/label']];
        }
    }

}
