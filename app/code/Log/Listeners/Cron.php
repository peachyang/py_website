<?php

namespace Seahinet\Log\Listeners;

use Seahinet\Lib\Listeners\ListenerInterface;

class Cron implements ListenerInterface
{

    use \Seahinet\Lib\Traits\Container,
        \Seahinet\Lib\Traits\DB;

    public function schedule()
    {
        $config = $this->getContainer()->get('config');
        if ($config['log/auto_cleaning']) {
            $tableGateway = $this->getTableGateway('log_visitor');
            $delete = $tableGateway->getSql()->delete();
            $delete->where->lessThanOrEqualTo('created_at', date('Y-m-d H:i:s', strtotime('-' . $config['log/expiration'] . 'days')));
            $tableGateway->deleteWith($delete);
        }
    }

}
