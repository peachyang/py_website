<?php

namespace Seahinet\Admin\ViewModel\RewardPoints;

use Seahinet\Customer\Model\Customer;
use Seahinet\Lib\ViewModel\Template;

class Edit extends Template
{

    public function getCurrentPoints()
    {
        if ($this->getQuery('id')) {
            $customer = new Customer;
            $customer->load($this->getQuery('id'));
            return (int) $customer->offsetGet('rewardpoints');
        }
        return 0;
    }

    public function getPoints()
    {
        if ($this->getQuery('id')) {
            $record = new \Seahinet\RewardPoints\Model\Collection\Record;
            $customer = new Customer;
            $record->where(['customer_id' => $this->getQuery('id'), 'status' => 1])
                    ->order('created_at desc');
            if (count($record)) {
                return $record;
            }
        }
        return [];
    }

}
