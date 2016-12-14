<?php

namespace Seahinet\Admin\Controller\Catalog\Attribute;

use Seahinet\Catalog\Model\Product;
use Seahinet\Lib\Controller\AuthActionController;
use Seahinet\Lib\Model\Eav\Type;
use Seahinet\Lib\Model\Eav\Attribute\Set as Model;

class SetController extends AuthActionController
{

    public function indexAction()
    {
        $root = $this->getLayout('admin_catalog_attribute_set_list');
        return $root;
    }

    public function editAction()
    {
        $root = $this->getLayout('admin_catalog_attribute_set_edit');
        $model = new Model;
        if ($id = $this->getRequest()->getQuery('id')) {
            $model->load($id);
            $root->getChild('head')->setTitle('Edit Product Attribute Set / Catalog Management');
        } else {
            $root->getChild('head')->setTitle('Add New Product Attribute Set / Catalog Management');
        }
        $root->getChild('edit', true)->setVariable('model', $model);
        return $root;
    }

    public function deleteAction()
    {
        return $this->doDelete('\\Seahinet\\Lib\\Model\\Eav\\Attribute\\Set', ':ADMIN/catalog_attribute_set/');
    }

    public function saveAction()
    {
        $response = $this->doSave('\\Seahinet\\Lib\\Model\\Eav\\Attribute\\Set', ':ADMIN/catalog_attribute_set/', ['name'], function($model, $data) {
            $type = new Type;
            $type->load(Product::ENTITY_TYPE, 'code');
            $model->setData('type_id', $type->getId());
        }
        );
        return $response;
    }

}
