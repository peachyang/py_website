<?php
namespace Seahinet\Admin\Controller\Resource;

use Seahinet\Lib\Controller\AuthActionController;
use Seahinet\Lib\Session\Segment;

use Seahinet\Resource\Model\Category as Model;

class CategoryController extends AuthActionController
{
    public function indexAction()
    {
        
        $root = $this->getLayout('admin_Resource_category_list');
        return $root;
        
    }

    public function editAction()
    {
        $root = $this->getLayout('admin_Resource_category_edit');
        if ($id = $this->getRequest()->getQuery('id')) {
            $model = new Model;
            $model->load($id);
            $root->getChild('edit', true)->setVariable('model', $model);
        }
        return $root;
    }
}
