<?php

namespace Seahinet\I18n\Model;

use Seahinet\Lib\Model\AbstractModel;

class Currency extends AbstractModel
{

    protected function _construct()
    {
        $this->init('i18n_currency', 'id', ['id', 'code', 'symbol', 'rate', 'format']);
    }

}
