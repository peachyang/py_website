<?php

namespace Seahinet\Lib\Model\Collection\Eav\Attribute;

use Seahinet\Lib\Model\AbstractCollection;

class Group extends AbstractCollection
{

    protected function construct()
    {
        $this->init('eav_attribute_group');
    }

}
