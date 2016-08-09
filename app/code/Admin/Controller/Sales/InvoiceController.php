<?php

namespace Seahinet\Admin\Controller\Sales;

use Seahinet\Lib\Controller\AuthActionController;

class InvoiceController extends AuthActionController
{

    public function indexAction()
    {
        $root = $this->getLayout('admin_sales_invoice_list');
        return $root;
    }

    public function viewAction()
    {
        if ($id = $this->getRequest()->getQuery('id')) {
            return $this->getLayout('admin_sales_invoice_view');
        }
        return $this->notFoundAction();
    }
    public function editAction()
    {
        $root = $this->getLayout('admin_sales_invoice_edit');
        if ($id = $this->getRequest()->getQuery('id')) {
            $model = new Model;
            $model->load($id);
            $root->getChild('edit', true)->setVariable('model', $model);
            $root->getChild('head')->setTitle('Edit Invoice / CMS');
        } else {
            $root->getChild('head')->setTitle('Add New Invoice / CMS');
        }
        return $root;
    }

    public function deleteAction()
    {
        return $this->doDelete('\\Seahinet\\Sales\\Model\\Invoice', ':ADMIN/sales_invoice/');
    }

    public function saveAction()
    {
        return $this->doSave('\\Seahinet\\Sales\\Model\\Invoice', ':ADMIN/sales_invoice/', [], function($model, $data) {
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
