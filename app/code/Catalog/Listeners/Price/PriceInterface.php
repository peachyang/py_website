<?php

namespace Seahinet\Catalog\Listeners\Price;

use Seahinet\Lib\Listeners\ListenerInterface;

interface PriceInterface extends ListenerInterface
{

    public function calc($event);
}
