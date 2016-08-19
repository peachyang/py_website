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
            if (function_exists('bcmul')) {
                $price = bcmul($price, $this->storage['rate']);
            } else {
                $price *= $this->storage['rate'];
            }
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
