<?php

namespace Seahinet\Retailer\Listeners;

use Seahinet\Lib\Listeners\ListenerInterface;
use Seahinet\Retailer\Model\Retailer;

class Customer implements ListenerInterface
{

    use \Seahinet\Lib\Traits\Container,
        \Seahinet\Lib\Traits\DB,
        \Seahinet\Lib\Traits\DataCache;

    public function afterRemove($e)
    {
        $model = new Retailer;
        $model->load($e['model']->getId(), 'customer_id');
        if ($model->getId()) {
            $this->getTableGateway('product_entity')->update(['status' => 0], ['store_id' => $model['store_id']]);
            $this->getContainer()->get('indexer')->reindex('product');
            $this->flushList('product');
            $model->remove();
        }
    }

}
