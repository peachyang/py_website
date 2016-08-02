<?php

namespace Seahinet\Catalog\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;

class Logview extends AbstractCollection
{

    protected function construct()
    {
        $this->init('log_view', 'customer_id,product_id', ['customer_id', 'product_id', 'created_at', 'updated_at']);
    }

}
