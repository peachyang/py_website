<?php

namespace Seahinet\RewardPoints\Model;

use Seahinet\Lib\Model\AbstractModel;

class Record extends AbstractModel
{

    protected function construct()
    {
        $this->init('reward_points', 'id', ['id', 'customer_id', 'order_id', 'count', 'comment', 'status']);
    }

}
