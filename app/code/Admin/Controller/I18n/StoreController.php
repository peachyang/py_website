<?php

namespace Seahinet\Admin\Controller\I18n;

use Seahinet\Lib\Controller\AuthActionController;
use Seahinet\Lib\Model\Store;

class StoreController extends AuthActionController
{

    public function editAction()
    {
        $root = $this->getLayout('admin_i18n_store_edit');
        if ($id = $this->getRequest()->getQuery('id')) {
            $model = new Store;
            $model->load($id);
            $root->getChild('edit', true)->setVariable('model', $model);
            $root->getChild('head')->setTitle('Edit Store');
        } else {
            $root->getChild('head')->setTitle('Add New Store');
        }
        return $root;
    }

    public function saveAction()
    {
        return $this->doSave('\\Seahinet\\Lib\\Model\\Store', ':ADMIN/i18n_language/list/', ['code', 'merchant_id']);
    }

    public function deleteAction()
    {
        return $this->doDelete('\\Seahinet\\Lib\\Model\\Store', ':ADMIN/i18n_language/list/');
    }

}
