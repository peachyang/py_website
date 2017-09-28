<?php

namespace Seahinet\Catalog\Listeners\Price;

use Seahinet\Lib\Listeners\ListenerInterface;

Abstract class AbstractPrice implements ListenerInterface
{

    use \Seahinet\Lib\Traits\Container;

    protected function getCurrency()
    {
        return $this->getContainer()->get('currency');
    }

    abstract public function calc($event);
}
