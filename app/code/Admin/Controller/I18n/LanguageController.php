<?php

namespace Seahinet\Admin\Controller\I18n;

use Seahinet\Lib\Controller\AuthActionController;
use Seahinet\Lib\Model\Language;
use Seahinet\I18n\Source\Locale;

class LanguageController extends AuthActionController
{

    public function listAction()
    {
        return $this->getLayout('admin_i18n_language_list');
    }

    public function editAction()
    {
        $root = $this->getLayout('admin_i18n_language_edit');
        if ($id = $this->getRequest()->getQuery('id')) {
            $model = new Language;
            $model->load($id);
            $root->getChild('edit', true)->setVariable('model', $model);
            $root->getChild('head')->setTitle('Edit Language');
        } else {
            $root->getChild('head')->setTitle('Add New Language');
        }
        return $root;
    }

    public function saveAction()
    {
        return $this->doSave('\\Seahinet\\Lib\\Model\\Language', ':ADMIN/i18n_language/list/', ['code', 'merchant_id'], function($model, $data) {
                    if (!isset($data['name']) || $data['name'] === '') {
                        $code = (new Locale)->getSourceArray($data['code']);
                        $model->setData('name', $code? : '');
                    }
                }
        );
    }

    public function deleteAction()
    {
        return $this->doDelete('\\Seahinet\\Lib\\Model\\Language', ':ADMIN/i18n_language/list/');
    }

}
