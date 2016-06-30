<?php

namespace Seahinet\Admin\ViewModel\Catalog\Edit\Product;

use Seahinet\Admin\ViewModel\Catalog\Grid\Product;

class Link extends Product
{

    protected $action = [];
    protected $type = '';

    public function __construct()
    {
        $this->setTemplate('admin/catalog/product/link');
    }

    public function getType()
    {
        return $this->type? : $this->getQuery('linktype');
    }

    public function setType($type)
    {
        $this->type = $type;
        $this->query = [];
        return $this;
    }

    public function getOrderByUrl($attr)
    {
        $query = $this->getQuery();
        if (isset($query['asc'])) {
            if ($query['asc'] == $attr) {
                unset($query['asc']);
                $query['desc'] = $attr;
            } else {
                $query['asc'] = $attr;
            }
        } else if (isset($query['desc'])) {
            if ($query['desc'] == $attr) {
                unset($query['desc']);
                $query['asc'] = $attr;
            } else {
                $query['desc'] = $attr;
            }
        } else {
            $query['asc'] = $attr;
        }
        return $this->getAdminUrl('catalog_product/list/?linktype=' . $this->getType() . '&' . http_build_query($query));
    }

}
