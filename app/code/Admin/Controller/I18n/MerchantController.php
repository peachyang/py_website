<?php

namespace Seahinet\Admin\Controller\I18n;

use Seahinet\Lib\Controller\AuthActionController;
use Seahinet\Lib\Model\Merchant;

class MerchantController extends AuthActionController
{

    public function editAction()
    {
        $root = $this->getLayout('admin_i18n_merchant_edit');
        if ($id = $this->getRequest()->getQuery('id')) {
            $model = new Merchant;
            $model->load($id);
            $root->getChild('edit', true)->setVariable('model', $model);
            $root->getChild('head')->setTitle('Edit Merchant');
        } else {
            $root->getChild('head')->setTitle('Add New Merchant');
        }
        return $root;
    }

    public function saveAction()
    {
        return $this->doSave('\\Seahinet\\Lib\\Model\\Merchant', ':ADMIN/i18n_language/list/', ['code']);
    }

    public function deleteAction()
    {
        return $this->doDelete('\\Seahinet\\Lib\\Model\\Merchant', ':ADMIN/i18n_language/list/');
    }

}
