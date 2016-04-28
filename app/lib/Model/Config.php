<?php

namespace Seahinet\Lib\Model;

class Config extends AbstractModel
{

    protected function _construct()
    {
        $this->init('core_config', 'id', ['id', 'merchant_id', 'store_id', 'language_id', 'path', 'value']);
    }

}
