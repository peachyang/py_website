<?php

namespace Seahinet\Lib\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;

class Store extends AbstractCollection
{

    protected function _construct()
    {
        $this->init('core_store');
    }

}