<?php

namespace Seahinet\Lib\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;

class Merchant extends AbstractCollection
{

    protected function _construct()
    {
        $this->init('core_merchant');
    }

}
