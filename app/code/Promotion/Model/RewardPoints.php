<?php

namespace Seahinet\Promotion\Model;

use Seahinet\Lib\Model\AbstractModel;

class RewardPoints extends AbstractModel
{

    protected function construct()
    {
        $this->init('reward_points', 'id', ['id', 'customer_id', 'order_id', 'count', 'comment', 'status']);
    }

}
