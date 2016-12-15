<?php

namespace Seahinet\Sales\ViewModel;

use Seahinet\Sales\Model\Collection\Order as Collection;
use Seahinet\Lib\ViewModel\Template;

class Inquire extends Template
{

    public function getInquireies()
    {
        $collection = new Collection;
        $collection->where(['increment_id' => $this->getRequest()->getPost('increment_id')]);
        return $collection;
    }

}
