<?php

namespace Seahinet\Balance\Traits;

use Seahinet\Customer\Model\Customer;
use Seahinet\Customer\Model\Collection\Balance as Collection;
use Seahinet\Lib\Model\Collection\Language;
use Zend\Db\Sql\Expression;

trait Recalc
{

    public function recalc($customerId)
    {
        $collection = new Collection;
        $collection->columns(['customer_id', 'amount' => new Expression('sum(amount)')])
                ->where([
                    'customer_id' => $customerId,
                    'status' => 1
                ])->group('customer_id');
        $collection->load(false, true);
        $balances = count($collection) ? $collection->toArray()[0]['amount'] : 0;
        $languages = new Language;
        $languages->columns(['id']);
        $languages->load(true, true);
        foreach ($languages as $language) {
            $customer = new Customer($language['id']);
            $customer->load($customerId);
            $customer->setData('balance', (float) $balances)
                    ->save();
        }
    }

}
