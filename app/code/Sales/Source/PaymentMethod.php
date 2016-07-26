<?php

namespace Seahinet\Sales\Source;

use Seahinet\Lib\Source\SourceInterface;

class PaymentMethod implements SourceInterface
{

    use \Seahinet\Lib\Traits\Container;

    public function getSourceArray()
    {
        $config = $this->getContainer()->get('config');
        $result = [];
        foreach ($config['system']['payment']['children'] as $code => $info) {
            if ($config['payment/' . $code . '/enable']) {
                $result[$code] = $config['payment/' . $code . '/label'];
            }
        }
        return $result;
    }

}
