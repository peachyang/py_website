<?php

namespace Seahinet\Log\Model;

use Seahinet\Lib\Model\AbstractModel;

class Visitor extends AbstractModel
{

    protected function construct()
    {
        $this->init('log_visitor', 'id', ['id', 'customer_id', 'store_id', 'product_id', 'session_id', 'http_referer', 'http_user_agent', 'http_accept_charset', 'http_accept_language']);
    }

}
