<?php

namespace Seahinet\I18n\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;

class Translation extends AbstractCollection
{

    protected function construct()
    {
        $this->init('i18n_translation');
    }

}
