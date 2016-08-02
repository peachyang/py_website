<?php

namespace Seahinet\Sales\Model\Order;

use Seahinet\Lib\Model\AbstractModel;
use Seahinet\Sales\Model\Collection\Order\Status as Collection;

class Phase extends AbstractModel
{

    protected function construct()
    {
        $this->init('sales_order_phase', 'id');
    }

    public function save($constraint = array(), $insertForce = false)
    {
        trigger_error('Call to undefined method Seahinet\\Sales\\Model\\Order\\Phase::save()', E_USER_ERROR);
    }

    public function getStatus()
    {
        if ($this->getId()) {
            $status = new Collection;
            $status->where(['phase_id' => $this->getId()]);
            return $status;
        }
        return [];
    }

    public function getDefaultStatus()
    {
        if ($this->getId()) {
            $status = new Collection;
            $status->where(['phase_id' => $this->getId(), 'is_default' => 1])->limit(1);
            if (count($status)) {
                return $status[0];
            }
        }
        return null;
    }

}
