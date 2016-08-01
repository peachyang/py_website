<?php

namespace Seahinet\Sales\Source;

use Seahinet\Lib\Source\SourceInterface;
use Seahinet\Payment\Model\AbstractMethod;

class PaymentMethod implements SourceInterface
{

    use \Seahinet\Lib\Traits\Container;

    public function getSourceArray()
    {
        $config = $this->getContainer()->get('config');
        $result = [];
        foreach ($config['system']['payment']['children'] as $code => $info) {
            $className = $config['payment/' . $code . '/model'];
            $model = new $className;
            if ($model instanceof AbstractMethod && $model->isValid()) {
                $result[$code] = $config['payment/' . $code . '/label'];
            }
        }
        return $result;
    }

}
