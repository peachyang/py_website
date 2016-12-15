<?php

namespace Seahinet\Log\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;

class Visitor extends AbstractCollection
{

    protected function construct()
    {
        $this->init('log_visitor');
    }

}
