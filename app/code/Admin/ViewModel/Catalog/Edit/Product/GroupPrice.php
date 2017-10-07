<?php

namespace Seahinet\Admin\ViewModel\Catalog\Edit\Product;

use Seahinet\Customer\Model\Collection\Group;
use Seahinet\Lib\ViewModel\Template;

class GroupPrice extends Template
{

    protected $template = 'admin/catalog/product/price/group';

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
