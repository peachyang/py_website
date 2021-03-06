<?php

namespace Seahinet\Shipping\Source;

use Seahinet\Lib\Source\SourceInterface;

class Method implements SourceInterface
{

    use \Seahinet\Lib\Traits\Container,
        \Seahinet\Lib\Traits\Translate;

    public function getSourceArray()
    {
        $config = $this->getContainer()->get('config');
        $result = [];
        foreach ($config['system']['shipping']['children'] as $code => $info) {
            $result[$code] = $this->translate($config['shipping/' . $code . '/label']);
        }
        return $result;
    }

}
