<?php

namespace Seahinet\Checkout\ViewModel\Cart;

use Seahinet\Lib\ViewModel\Template;

class Item extends Template
{

    protected static $format = null;

    public function getCurrency()
    {
        return $this->getContainer()->get('currency');
    }

    public function getFormat()
    {
        if (is_null(static::$format)) {
            $currency = $this->getCurrency();
            static::$format = preg_replace('/\%(?:\d+\$)?s/', $currency->offsetGet('symbol'), $currency->offsetGet('format'));
        }
        return static::$format;
    }

}
