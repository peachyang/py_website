<?php

namespace Seahinet\RewardPoints\ViewModel;

use Seahinet\Lib\Session\Segment;
use Seahinet\Lib\ViewModel\Template;
use Seahinet\RewardPoints\Model\Collection\Record;

class Statement extends Template
{

    protected $customerId = null;

    public function getCustomerId()
    {
        if (is_null($this->customerId)) {
            $customer = $this->getVariable('customer');
            if ($customer) {
                $this->customerId = $customer->getId();
            } else {
                $segment = new Segment('customer');
                $this->customerId = $segment->get('customer')->getId();
            }
        }
        return $this->customerId;
    }

    public function getStatement()
    {
        if ($this->getCustomerId()) {
            $record = new Record;
            $record->where(['customer_id' => $this->getCustomerId()])
                    ->order('created_at DESC')
                    ->limit(20)
                    ->offset(((int) $this->getQuery('page', 1) - 1) * 20);
            if (count($record)) {
                return $record;
            }
        }
        return [];
    }

}
