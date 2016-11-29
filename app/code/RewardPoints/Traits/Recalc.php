<?php

namespace Seahinet\RewardPoints\Traits;

use Seahinet\Customer\Model\Customer;
use Seahinet\RewardPoints\Model\Collection\Record as Collection;
use Zend\Db\Sql\Expression;

trait Recalc
{

    public function recalc($customerId)
    {
        $collection = new Collection;
        $collection->columns(['customer_id', 'count' => new Expression('sum(count)')])
                ->where(['customer_id' => $customerId])
                ->group('customer_id');
        $customer = new Customer;
        $customer->load($customerId);
        $customer->setData('reward_points', count($collection) ? $collection[0]['count'] : 0)
                ->save();
    }

}
