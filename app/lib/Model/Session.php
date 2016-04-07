<?php

namespace Seahinet\Lib\Model;

class Session extends AbstractModel
{

    protected function _construct()
    {
        $this->init('core_session', 'id', ['id', 'data']);
    }

}
