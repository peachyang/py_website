<?php

namespace Seahinet\Checkout\ViewModel\Cart;

use Seahinet\I18n\Model\Currency;
use Seahinet\Lib\ViewModel\Template;

class Item extends Template
{

    protected static $currency = null;

    public function getCurrency()
    {
        if (is_null(static::$currency)) {
            static::$currency = new Currency;
            static::$currency->load($this->getRequest()->getCookie('currency', $this->getConfig()['i18n/currency/base']), 'code');
        }
        return static::$currency;
    }

}
