<?php

namespace Seahinet\Sales\Source;

use Seahinet\Lib\Source\SourceInterface;

class ShippingMethod implements SourceInterface
{

    use \Seahinet\Lib\Traits\Container;

    public function getSourceArray()
    {
        $config = $this->getContainer()->get('config');
        $result = [];
        foreach ($config['system']['shipping']['children'] as $code => $info) {
            if ($config['shipping/' . $code . '/enable']) {
                $result[$code] = $config['shipping/' . $code . '/label'];
            }
        }
        return $result;
    }

}
