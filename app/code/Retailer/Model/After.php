<?php

namespace Seahinet\Retailer\Model;

use Seahinet\Lib\Model\AbstractModel;
use Seahinet\Retailer\Model\Collection\After as Collection;

/**
 * Description of After
 *
 * @author peach
 */
class After extends AbstractModel
{

    protected function construct()
    {
        $this->init('rma', 'id', ['id', 'order_id', 'customer_id', 'carrier', 'track_number', 'comment', 'status', 'created_at']);
    }

}
