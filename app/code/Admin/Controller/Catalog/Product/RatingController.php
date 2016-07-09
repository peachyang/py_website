<?php

namespace Seahinet\Admin\Controller\Catalog\Product;

use Seahinet\Lib\Controller\AuthActionController;
use Seahinet\Catalog\Model\Product\Rating as Model;

class RatingController extends AuthActionController
{

    public function indexAction()
    {
        $root = $this->getLayout('admin_catalog_product_rating_list');
        return $root;
    }

    public function editAction()
    {
        $root = $this->getLayout('admin_catalog_product_rating_edit');
        if ($query = $this->getRequest()->getQuery('id')) {
            $model = new Model;
            $model->load($query);
            $root->getChild('edit', TRUE)->setVariable('model', $model);
            $root->getChild('head')->setTitle('Edit Rating / Rating Management');
        } else {
            $root->getChild('head')->setTitle('Add New Rating / Rating Management');
        }
        return $root;
    }

    public function deleteAction()
    {
        return $this->doDelete('\\Seahinet\\Catalog\\Model\\Product\\Rating', ':ADMIN/catalog_product_rating/');
    }

    public function saveAction()
    {
        return $this->doSave('\\Seahinet\\Catalog\\Model\\Product\\Rating', ':ADMIN/catalog_product_rating/');
    }

}
