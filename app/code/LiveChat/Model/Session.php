<?php

namespace Seahinet\LiveChat\Model;

use Seahinet\Lib\Model\AbstractModel;

class Session extends AbstractModel
{

    protected function construct()
    {
        $this->init('livechat_session', 'id', ['id', 'customer_id_1', 'customer_id_2']);
    }

}
