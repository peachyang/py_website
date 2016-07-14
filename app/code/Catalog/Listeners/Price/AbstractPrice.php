<?php

namespace Seahinet\Catalog\Listeners\Price;

use Seahinet\I18n\Model\Currency;
use Seahinet\Lib\Listeners\ListenerInterface;

Abstract class AbstractPrice implements ListenerInterface
{

    use \Seahinet\Lib\Traits\Container;

    private static $currency = null;

    protected function getCurrency()
    {
        if (is_null(self::$currency)) {
            self::$currency = new Currency;
            self::$currency->load($this->getContainer()->get('request')->getCookie('currency', $this->getContainer()->get('config')['i18n/currency/base']), 'code');
        }
        return self::$currency;
    }

    abstract public function calc($event);
}
