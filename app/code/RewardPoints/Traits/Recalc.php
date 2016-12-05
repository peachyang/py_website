<?php

namespace Seahinet\RewardPoints\Traits;

use Seahinet\Customer\Model\Customer;
use Seahinet\RewardPoints\Model\Collection\Record as Collection;
use Seahinet\Lib\Model\Collection\Language;
use Zend\Db\Sql\Expression;

trait Recalc
{

    public function recalc($customerId)
    {
        $collection = new Collection;
        $collection->columns(['customer_id', 'count' => new Expression('sum(count)')])
                ->where([
                    'customer_id' => $customerId,
                    'status' => 1
                ])->group('customer_id');
        $languages = new Language;
        $languages->columns(['id']);
        $languages->load(true, true);
        foreach ($languages as $language) {
            $customer = new Customer($language['id']);
            $customer->load($customerId);
            $customer->setData('rewardpoints', count($collection) ? $collection[0]['count'] : 0)
                    ->save();
        }
    }

}
