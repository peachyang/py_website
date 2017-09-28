<?php

namespace Seahinet\Admin\Controller\Article\Product;

use Seahinet\Lib\Controller\AuthActionController;
use Seahinet\Article\Model\Product\Review as Model;

class ReviewController extends AuthActionController
{

    public function indexAction()
    {
        $root = $this->getLayout('admin_article_review_list');
        return $root;
    }

    public function editAction()
    {
        $root = $this->getLayout('admin_article_review_edit');
        if ($query = $this->getRequest()->getQuery('id')) {
            $model = new Model;
            $model->load($query);
            $root->getChild('edit', TRUE)->setVariable('model', $model);
            $root->getChild('head')->setTitle('Edit Review / Review Management');
        } else {
            $root->getChild('head')->setTitle('Add New Review / Review Management');
        }
        return $root;
    }

    public function deleteAction()
    {
        return $this->doDelete('\\Seahinet\\Article\\Model\\Product\\Review', ':ADMIN/article_review/');
    }

    public function saveAction()
    {
        return $this->doSave('\\Seahinet\\Article\\Model\\Product\\Review', ':ADMIN/article_review/', ['product_id'], function($model, $data) {
                    if ($data['customer_id'] === '') {
                        $model['customer_id'] = null;
                    }
                    if (empty($data['id'])) {
                        $model->setId(null);
                    }
                }
        );
    }

}
