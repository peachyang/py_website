<?php

namespace Seahinet\Admin\ViewModel\Article\Edit\Product;

use Seahinet\Customer\Model\Collection\Group;
use Seahinet\Lib\ViewModel\Template;

class TierPrice extends Template
{

    protected $template = 'admin/article/product/price/tier';

    public function getPrice()
    {
        $value = $this->getVariable('item')['value'];
        return $value ? json_decode($value, true) : [];
    }

    public function getGroups()
    {
        return new Group;
    }

}
