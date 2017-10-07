<?php

namespace Seahinet\Lib\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;

class Cron extends AbstractCollection
{

    protected function construct()
    {
        $this->init('core_schedule');
    }

}
