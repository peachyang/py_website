<?php

namespace Seahinet\Checkout\ViewModel\Cart;

use Seahinet\I18n\Model\Currency;
use Seahinet\Lib\ViewModel\Template;

class Item extends Template
{

    protected static $currency = null;

    public function getCurrency()
    {
        if (is_null(self::$currency)) {
            self::$currency = new Currency;
            self::$currency->load($this->getRequest()->getCookie('currency', $this->getConfig()['i18n/currency/base']), 'code');
        }
        return self::$currency;
    }

}
