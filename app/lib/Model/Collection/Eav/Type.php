<?php

namespace Seahinet\Lib\Model\Collection\Eav;

use Seahinet\Lib\Model\AbstractCollection;

class Type extends AbstractCollection
{

    protected function construct()
    {
        $this->init('eav_entity_type');
    }

}
