<?php

namespace Seahinet\Admin\ViewModel\Resource;

use Seahinet\Resource\Source\Category;
use Seahinet\Lib\Session\Segment;
use Seahinet\Lib\Source\Store;

class Modal extends Explorer
{

    public function getCategorySource()
    {
        return (new Category)->getSourceArray();
    }

    public function getSubmitUrl()
    {
        return $this->getAdminUrl('resource_resource/upload/');
    }

    public function getStore()
    {
        $segment = new Segment('admin');
        $store = $segment->get('user')->getStore();
        if ($store) {
            return $store->getId();
        } else {
            return (new Store)->getSourceArray();
        }
    }

    public function getChildrenCategories($id = 0, $title = null)
    {
        $child = parent::getChildrenCategories($id, $title);
        $child->setVariable('prefix', 'modal-upload-');
        return $child;
    }

}
