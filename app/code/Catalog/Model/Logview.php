<?php

namespace Seahinet\Catalog\Model;

use Seahinet\Lib\Model\AbstractModel;
use Seahinet\Catalog\Model\Collection\Logview as Collection;

class Logview extends AbstractModel
{

    protected function construct()
    {
        $this->init('log_view', 'customer_id,product_id', ['customer_id', 'product_id', 'created_at', 'updated_at']);
    }

    public function getLogview()
    {
        if ($this->getId()) {
            $tracks = new Collection;
            $tracks->Where(['product_id' => $this->getId()])
                    ->order('created_at');
            return $tracks;
        }
        return [];
    }

}
