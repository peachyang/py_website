<?php

namespace Seahinet\Sales\Source;

use Seahinet\Lib\Source\SourceInterface;
use Seahinet\Shipping\Model\AbstractMethod;

class ShippingMethod implements SourceInterface
{

    use \Seahinet\Lib\Traits\Container;

    public function getSourceArray()
    {
        $config = $this->getContainer()->get('config');
        $result = [];
        foreach ($config['system']['shipping']['children'] as $code => $info) {
            $className = $config['shipping/' . $code . '/model'];
            $model = new $className;
            if ($model instanceof AbstractMethod && $model->isValid()) {
                $result[$code] = $config['shipping/' . $code . '/label'];
            }
        }
        return $result;
    }

}
