<?php

namespace Seahinet\Lib\Model;

class Translate extends AbstractModel
{

    protected function _construct()
    {
        $this->init('core_translate', 'id', ['id', 'string', 'translate', 'locale', 'status']);
    }

}
