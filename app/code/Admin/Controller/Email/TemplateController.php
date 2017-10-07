<?php

namespace Seahinet\Admin\Controller\Email;

use Seahinet\Email\Model\Template as Model;
use Seahinet\Lib\Controller\AuthActionController;

class TemplateController extends AuthActionController
{

    public function indexAction()
    {
        $root = $this->getLayout('admin_email_template_list');
        return $root;
    }

    public function editAction()
    {
        $root = $this->getLayout('admin_email_template_edit');
        if ($id = $this->getRequest()->getQuery('id')) {
            $model = new Model;
            $model->load($id);
            $root->getChild('edit', true)->setVariable('model', $model);
            $root->getChild('head')->setTitle('Edit Template / Email Template');
        } else {
            $root->getChild('head')->setTitle('Add New Template / Email Template');
        }
        return $root;
    }

    public function saveAction()
    {
        return $this->doSave('\\Seahinet\\Email\\Model\\Template', ':ADMIN/email_template/', ['code', 'language_id', 'subject']);
    }

    public function deleteAction()
    {
        return $this->doDelete('\\Seahinet\\Email\\Model\\Template', ':ADMIN/email_template/');
    }

}
