<?php

namespace Seahinet\Admin\Controller\Cms;

use Seahinet\Cms\Model\Page as Model;
use Seahinet\Lib\Controller\AuthActionController;
use Seahinet\Lib\Session\Segment;

class PageController extends AuthActionController
{

    public function indexAction()
    {
        $root = $this->getLayout('admin_cms_page_list');
        return $root;
    }

    public function editAction()
    {
        $root = $this->getLayout('admin_cms_page_edit');
        if ($id = $this->getRequest()->getQuery('id')) {
            $model = new Model;
            $model->load($id);
            $root->getChild('edit', true)->setVariable('model', $model);
            $root->getChild('head')->setTitle('Edit Page / CMS');
        } else {
            $root->getChild('head')->setTitle('Add New Page / CMS');
        }
        return $root;
    }

    public function deleteAction()
    {
        return $this->doDelete('\\Seahinet\\Cms\\Model\\Page', ':ADMIN/cms_page/');
    }

    public function saveAction()
    {
        $response = $this->doSave('\\Seahinet\\Cms\\Model\\Page', ':ADMIN/cms_page/', ['language_id', 'uri_key', 'title'], function($model, $data) {
                    $user = (new Segment('admin'))->get('user');
                    if ($user->getStore()) {
                        if ($model->getId() && $model->offsetGet('store_id') != $user->getStore()->getId()) {
                            throw new \Exception('Not allowed to save.');
                        }
                        $model->setData('store_id', $user->getStore()->getId());
                    } else if (!isset($data['store_id']) || (int) $data['store_id'] === 0) {
                        $model->setData('store_id', null);
                    }
                }
        );
        $this->getContainer()->get('indexer')->reindex('cms_url');
        return $response;
    }

}
