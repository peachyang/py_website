<?php

namespace Seahinet\I18n\Model;

use Seahinet\Lib\Model\AbstractModel;

class Currency extends AbstractModel
{

    protected function construct()
    {
        $this->init('i18n_currency', 'id', ['id', 'code', 'symbol', 'rate', 'format']);
    }

    public function convert($price, $format = false)
    {
        if (isset($this->storage['rate'])) {
            $price *= $this->storage['rate'];
        }
        return $format ? $this->format($price) : $price;
    }

    public function format($price)
    {
        if (isset($this->storage['format'])) {
            return sprintf($this->storage['format'], $this->storage['symbol'], $price);
        }
        return $price;
    }

}
