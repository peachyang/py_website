<?php

namespace Seahinet\Promotion\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;

class Condition extends AbstractCollection
{

    protected function construct()
    {
        $this->init('promotion_condition');
    }

}
