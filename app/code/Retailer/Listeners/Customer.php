<?php

namespace Seahinet\Retailer\Listeners;

use Seahinet\Lib\Listeners\ListenerInterface;
use Seahinet\Retailer\Model\Retailer;

class Customer implements ListenerInterface
{

    public function afterRemove($e)
    {
        $model = new Retailer;
        $model->load($e['model']->getId(), 'customer_id');
        if ($model->getId()) {
            $model->remove();
        }
    }

}
