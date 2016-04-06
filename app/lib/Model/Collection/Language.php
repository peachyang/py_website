<?php

namespace Seahinet\Lib\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;

class Language extends AbstractCollection
{

    protected function _construct()
    {
        $this->init('core_language');
    }

}
