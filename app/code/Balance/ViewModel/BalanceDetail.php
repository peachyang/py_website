<?php

namespace Seahinet\Balance\ViewModel;

use Seahinet\Lib\Session\Segment;
use Seahinet\Customer\ViewModel\Account;
use Seahinet\Customer\Model\Collection\Balance;
use Zend\Db\Sql\Expression;

class BalanceDetail extends Account
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
            $balance = new Balance;
            $balance->where(['customer_id' => $this->getCustomerId()])
                    ->order('status DESC , created_at DESC')
                    ->limit(20)
                    ->offset(((int) $this->getQuery('page', 1) - 1) * 20);
            if (count($balance)) {
                return $balance;
            }
        }
        return [];
    }

    public function getAmount()
    {
        if ($this->getCustomerId()) {
            $balance = new Balance;
            $balance->columns(['amount' => new Expression('sum(amount)')])
                    ->where([
                        'customer_id' => $this->getCustomerId(),
                        'status' => 1
            ]);
            $points = (count($balance) ? $balance[0]['amount'] : 0);
            return (float) $points;
        }
    }

}
