<?php

namespace Seahinet\Admin\Controller\Api\Oauth;

use Seahinet\Lib\Controller\AuthActionController;
use Seahinet\Oauth\Model\Consumer as Model;

class ConsumerController extends AuthActionController
{

    public function indexAction()
    {
        return $this->getLayout('admin_oauth_consumer_list');
    }

    public function editAction()
    {
        $root = $this->getLayout('admin_oauth_consumer_edit');
        $model = new Model;
        if ($id = $this->getRequest()->getQuery('id')) {
            $model->load($id);
            $root->getChild('head')->setTitle('Edit Consumer');
        } else {
            $root->getChild('head')->setTitle('Add New Consumer');
        }
        $root->getChild('edit', true)->setVariable('model', $model);
        return $root;
    }

    public function deleteAction()
    {
        return $this->doDelete('\\Seahinet\\Oauth\\Model\\Consumer', ':ADMIN/api_oauth_consumer/');
    }

    public function saveAction()
    {
        return $this->doSave('\\Seahinet\\Oauth\\Model\\Consumer', ':ADMIN/api_oauth_consumer/', ['name', 'key', 'secret', 'callback_url']);
    }

}
