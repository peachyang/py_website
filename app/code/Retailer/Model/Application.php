<?php

namespace Seahinet\Retailer\Model;

use Seahinet\Lib\Model\AbstractModel;
use Seahinet\Retailer\Model\Collection\Application as Collection;

class Application extends AbstractModel
{

    protected function construct()
    {
        $this->init('retailer_application', 'customer_id', ['customer_id', 'lisence_1', 'lisence_2', 'phone', 'brand_type', 'product_type', 'status']);
    }

    protected function isUpdate($constraint = array(), $insertForce = false)
    {
        if (parent::isUpdate($constraint, $insertForce)) {
            $collection = new Collection;
            $collection->where(['customer_id' => $this->storage['customer_id']]);
            return (bool) $collection->count();
        }
        return false;
    }

}
