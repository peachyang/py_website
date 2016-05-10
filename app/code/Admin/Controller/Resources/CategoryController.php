<?php
namespace Seahinet\Admin\Controller\Resources;

use Seahinet\Lib\Controller\AuthActionController;
use Seahinet\Lib\Session\Segment;

use Seahinet\Resources\Model\Category as Model;

class CategoryController extends AuthActionController
{
    public function indexAction()
    {
        
        $root = $this->getLayout('admin_resources_category_list');
        return $root;
        
    }

    public function editAction()
    {
        $root = $this->getLayout('admin_resources_category_edit');
        if ($id = $this->getRequest()->getQuery('id')) {
            $model = new Model;
            $model->load($id);
            $root->getChild('edit', true)->setVariable('model', $model);
        }
        return $root;
    }
}
