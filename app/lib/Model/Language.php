<?php

namespace Seahinet\Lib\Model;

class Language extends AbstractModel
{

    protected function _construct()
    {
        $this->init('core_language', 'id', ['id', 'store', 'code', 'status']);
    }

}
