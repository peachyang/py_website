<?php

namespace Seahinet\RewardPoints\ViewModel;

use Seahinet\Lib\Session\Segment;
use Seahinet\Lib\ViewModel\Template;
use Seahinet\RewardPoints\Model\Collection\Record;
use Seahinet\Customer\Model\Customer;
use Zend\Db\Sql\Expression;

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

    public function getAvailablePoints()
    {
        if ($this->getCustomerId()) {
            $record = new Record;
            $record->columns(['count' => new Expression('sum(count)')])
                    ->where([
                        'customer_id' => $this->getCustomerId(),
                        'status' => 1
            ]);
            $record->load(FALSE, TRUE);
            $points = (count($record) ? $record[0]['count'] : 0);
            return (int) $points;
        }
        return 0;
    }

    public function getUnavailablePoints()
    {
        if ($this->getCustomerId()) {
            $record = new Record;
            $record->columns(['count' => new Expression('sum(count)')])
                    ->where([
                        'customer_id' => $this->getCustomerId(),
                        'comment' => 'Consumption',
                        'status' => 0
            ]);
            $record->load(FALSE, TRUE);
            $points = (count($record) ? $record[0]['count'] : 0);
            return (int) $points;
        }
        return 0;
    }

}
