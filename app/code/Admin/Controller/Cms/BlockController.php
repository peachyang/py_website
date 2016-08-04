<?php

namespace Seahinet\Admin\Controller\Cms;

use Seahinet\Cms\Model\Block as Model;
use Seahinet\Lib\Controller\AuthActionController;
use Seahinet\Lib\Session\Segment;

class BlockController extends AuthActionController
{

    public function indexAction()
    {
        $root = $this->getLayout('admin_cms_block_list');
        return $root;
    }

    public function editAction()
    {
        $root = $this->getLayout('admin_cms_block_edit');
        if ($id = $this->getRequest()->getQuery('id')) {
            $model = new Model;
            $model->load($id);
            $root->getChild('edit', true)->setVariable('model', $model);
            $root->getChild('head')->setTitle('Edit Block / CMS');
        } else {
            $root->getChild('head')->setTitle('Add New Block / CMS');
        }
        return $root;
    }

    public function deleteAction()
    {
        return $this->doDelete('\\Seahinet\\Cms\\Model\\Block', ':ADMIN/cms_block/');
    }

    public function saveAction()
    {
        return $this->doSave('\\Seahinet\\Cms\\Model\\Block', ':ADMIN/cms_block/', ['code', 'language_id'], function($model, $data) {
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
    }

}
