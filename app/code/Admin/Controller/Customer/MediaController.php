<?php

namespace Seahinet\Admin\Controller\Customer;

use Exception;
use Seahinet\Customer\Model\Media as Model;
use Seahinet\Lib\Controller\AuthActionController;

class MediaController extends AuthActionController
{

    public function indexAction()
    {
        $root = $this->getLayout('admin_media_list');
        return $root;
    }

    public function editAction()
    {
        $root = $this->getLayout('admin_media_edit');
        $model = new Model;
        if ($id = $this->getRequest()->getQuery('id')) {
            $model->load($id);
            $root->getChild('head')->setTitle('Edit Customer Media / Customer Management');
        } else {
            $root->getChild('head')->setTitle('Add New Customer Media / Customer Management');
        }
        $root->getChild('edit', true)->setVariable('model', $model);
        return $root;
    }

    public function deleteAction()
    {
        return $this->doDelete('\\Seahinet\\Customer\\Model\\Media', ':ADMIN/customer_media/');
    }

    public function saveAction()
    {
        return $this->doSave('\\Seahinet\\Customer\\Model\\Media', ':ADMIN/customer_media/', ['label', 'link', 'icon']);
    }

}
