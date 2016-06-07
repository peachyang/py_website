<?php

namespace Seahinet\Admin\ViewModel\Resource;

use Seahinet\Resource\Source\Category;
use Seahinet\Lib\Session\Segment;
use Seahinet\Lib\Source\Store;
use Seahinet\Lib\ViewModel\AbstractViewModel;

class Modal extends AbstractViewModel
{

    public function getCategorySource()
    {
        return (new Category)->getSourceArray();
    }

    public function getStore()
    {
        $segment = new Segment('admin');
        $store = $segment->get('user')->getStore();
        if($store){
            return $store->getId();
        }else{
            return (new Store)->getSourceArray();
        }
    }

}
