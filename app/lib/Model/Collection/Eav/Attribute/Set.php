<?php

namespace Seahinet\Lib\Model\Collection\Eav\Attribute;

use Seahinet\Lib\Model\AbstractCollection;

class Set extends AbstractCollection
{

    protected function construct()
    {
        $this->init('eav_attribute_set');
    }

}
