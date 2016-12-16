<?php

namespace Seahinet\Email\Model;

use Seahinet\Lib\Model\AbstractModel;

class Queue extends AbstractModel
{

    protected function construct()
    {
        $this->init('email_queue', 'id', ['id', 'from', 'to', 'template_id', 'status', 'scheduled_at', 'finished_at']);
    }

}
