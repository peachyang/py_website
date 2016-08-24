<?php

namespace Seahinet\Admin\Controller\Sales;

use Seahinet\Lib\Controller\AuthActionController;
use Seahinet\Sales\Model\Rma;

class RefundController extends AuthActionController
{

    use \Seahinet\Lib\Traits\DB;
    public function indexAction()
    {
        $root = $this->getLayout('admin_refund_list');
        return $root;
    }
    
    public function viewAction()
    {
        $root = $this->getLayout('admin_refund_view');
        return $root;
    }
    
    public function statusAction()
    {
        if ($this->getRequest()->isPost()){
            $post = $this->getRequest()->getPost();
            $refund = new Rma;
            $refund->load($post['id']);
            $refund->setData('status', $post['status'])->save();
        }
        return $this->response(isset($result) ? $result : ['error' => 0, 'message' => []], 'admin/sales_refund/');
    }
    
    
    public function processingAction()
    {
        if ($id = $this->getRequest()->getQuery('id')){
            $refund = new Rma;
            $refund->load($id);
            $refund->setData('status', 0)->save();
            $result['error'] = 0;
            $result['message'][] = ['message' => $this->translate('', []), 'level' => 'success'];
            return $this->response($result, $this->getRequest()->getHeader('HTTP_REFERER'));
        }
        return $this->redirectReferer(':ADMIN/sales_refund/');
    }
    
    
    public function completeAction()
    {
        if ($id = $this->getRequest()->getQuery('id')){
            $refund = new Rma;
            $refund->load($id);
            $refund->setData('status', 1)->save();
            $result['error'] = 0;
            $result['message'][] = ['message' => $this->translate('', []), 'level' => 'success'];
            return $this->response($result, $this->getRequest()->getHeader('HTTP_REFERER'));
        }
        return $this->redirectReferer(':ADMIN/sales_refund/');
    }
    

}
