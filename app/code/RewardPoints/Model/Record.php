<?php

namespace Seahinet\RewardPoints\Model;

use Seahinet\Lib\Model\AbstractModel;

class Record extends AbstractModel
{

    use \Seahinet\RewardPoints\Traits\Recalc;
    
    protected function construct()
    {
        $this->init('reward_points', 'id', ['id', 'customer_id', 'order_id', 'count', 'comment', 'status']);
    }

    protected function afterSave()
    {
        $this->recalc($this->storage['customer_id']);
        parent::afterSave();
    }

}
