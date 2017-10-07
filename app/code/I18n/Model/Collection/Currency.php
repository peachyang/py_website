<?php

namespace Seahinet\I18n\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;

class Currency extends AbstractCollection
{

    protected function construct()
    {
        $this->init('i18n_currency');
    }

}
