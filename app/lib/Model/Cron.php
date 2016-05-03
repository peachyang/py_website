<?php

namespace Seahinet\Lib\Model;

class Cron extends AbstractModel
{

    protected function _construct()
    {
        $this->init('core_schedule', 'id', ['id', 'messages', 'code', 'status', 'created_at', 'scheduled_at', 'executed_at', 'finished_at']);
    }

}
